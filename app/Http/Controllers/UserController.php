<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Http\Resources\ReferralLogResource;
use App\Http\Resources\UserResource;
use App\Mail\ContactResponse;
use App\Models\ReferralLog;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function transaction()
    {
        return Transaction::where('user_id', \request()->user()->id)->orderByDesc('created_at')->paginate(10);
    }

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->orWhere('username', $request->username)->first();
//
        if ($user && \Hash::check($request->password, $user->password)){
//            if (!$user->verified){
//                throw new ApiException('Account Inactive Contact Us');
//            }
//            Auth::login($user);
          return $user->createToken('test')->plainTextToken;
        }
        return new JsonResponse([
            'message' => 'invalid credentials',
        ], 400);
    }

    public function updateUserPassword( Request $request): JsonResponse
    {
        $input = $request->all();
        Validator::make($input, [
            'current_password' => ['required', 'string', 'current_password:sanctum'],
            'password' => $this->passwordRules(),
        ], [
            'current_password.current_password' => __('The provided password does not match your current password.'),
        ])->stopOnFirstFailure(true)->validate();
        $user = \request()->user();

//        if (!Hash::check($request->password, $user->password)){
//            return new JsonResponse([
//               'message' => 'Current Password Did Not Match',
//                'status' => 'error',
//
//            ], Response::HTTP_UNPROCESSABLE_ENTITY);
//        }
        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();

        return new JsonResponse([
            'message' => 'password updated successfully',
            'status' => 'success'
        ], Response::HTTP_OK);
    }
    protected function passwordRules(): array
    {
        return ['bail','required', 'string', 'confirmed'];
    }
/**
     * Display a listing of the level resource.
     */

    public function level(Request $request)
    {

    }


    public function profileUpdate(Request $request)
    {
        $request->validate([
           'image' => 'nullable|mimes:jpg,png,jpeg',
           'firstName' => 'required',
           'lastName' => 'required',
           'phone' => 'required',
           'email' => 'email|required',
        ]);
        $user = $request->user();
        $user->firstname = $request->firstName;
        $user->lastname = $request->lastName;
        $user->phone = $request->phone;
        $user->email = $request->email;
        if ($request->hasFile('image') && !$user->image){
            $amount = gs()->profile_point;
            $user->balance += $amount;
            makeTransation(user_id: $user->id,trx: getTrx(), type: '+', amount: $amount, remark: "Profile Picture Point");
           $user->save();
        }
        if ($request->hasFile('image')){
            $imageName = time().'.'.$request->file('image')->extension();
            $imagePath = $request->file('image')->move(base_path('images'), $imageName);
            if(\File::exists(base_path("images/$user->image"))){
                \File::delete(base_path("images/$user->image"));
            }
            $user->image = $imageName;
        }
        $user->save();
        return new JsonResponse([
            'message' => 'Profile Update Successfully',
            'user' => $user
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'message' => ['required', 'string', 'min:10']
        ]);
        $user = $request->user();
        Mail::to($user)->send(new ContactResponse($user));
        return new JsonResponse([
            'message' => 'Message Sent Successful'
        ]);
    }


    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function dailyLogin(Request $request)
    {
        $users  = User::all();
        $settings  = gs();

        foreach ($users as $user){
            if ($user->last_login < now() && !$user->last_login->isToday()){
                $user->balance += $settings->daily_point;
                $user->save();
            }
            makeTransation($user->id, getTrx(), '+', $settings->daily_point, 'Daily Reward');
        }


    }
    public function referral(Request $request)
    {

        $referral = ReferralLog::where('user_id', \request()->user()->id)->orderByDesc('created_at')->get();
        return ReferralLogResource::collection($referral);
    }
    /**
     * @throws ApiException
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'username' => ['required', 'exists:users,username'],
            'amount' => 'required'
        ]);
        $user = \auth()->user();
        $receiver = User::where('username', $request->username)->first();
        $amount = $request->amount;
        if (!$user->username){
        throw new ApiException("Claim Your username before sending point");

    }
        if ($user->balance < $amount){
            throw new ApiException("Insufficient Point");

        }
        if ($user->username == $request->username){
            throw new ApiException("You cant transfer to your own account");
        }

        if (!$receiver){
            throw new ApiException("Receiver Not Found");

        }
        DB::transaction(function () use ($request, $receiver, $user, $amount){
            $user->balance -= $amount;
            $user->save();
            $receiver->balance += $amount;
            $receiver->save();
        });
        makeTransation(user_id: $user->id,trx: getTrx(), type: '+', amount: $amount, remark: "Transfer of $amount SP to $receiver->username");
        makeTransation(user_id: $receiver->id,trx: getTrx(), type: '-', amount: $amount, remark: "Received $amount SP From $user->username");
        return new JsonResponse([
            'message' => 'Transaction Successful',
            'data' => [
                'user_balance' => $user->balance
            ]
        ]);
    }
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ApiException
     */
    public function claimUsername(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users,username'
        ]);
        if ($request->user()->username){
            throw new ApiException("Username Already Claimed");
        }
        $user = \auth()->user();
        $amount =gs()->username_point;
        $user->balance += $amount;
        $user->username = $request->username;
        $user->save();
        makeTransation(user_id: auth()->id(), trx: getTrx(), type: '+', amount: $amount, remark: 'Username Reward');
        return new JsonResponse([
            'message' => 'Username Claim Successful',
            'username' => $user->username
        ]);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ApiException
     */
    public function claimTwitter(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users,twitter_username'
        ], [
            'username.unique' => 'You Already Follow And Earn Point'
        ]);
        if ($request->user()->twitter_username){
            throw new ApiException("Twitter Username Already Claimed");
        }
        $user = \auth()->user();
        $amount =gs()->twitter_point;
        $user->balance += $amount;
        $user->twitter_username = $request->username;
        $user->save();
        makeTransation(user_id: auth()->id(), trx: getTrx(), type: '+', amount: $amount, remark: 'Twitter Follow Reward');
        return new JsonResponse([
            'message' => 'Twitter Point Claim Successful',
            'twitter' => $user->twitter_username
        ]);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ApiException
     */
    public function claimTelegram(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users,telegram_username'
        ], [
            'username.unique' => 'You Already Follow And Earn Point'
        ]);
        if ($request->user()->telegram_username){
            throw new ApiException("Telegram Username Already Claimed");
        }
        $user = \auth()->user();
        $amount =gs()->telegram_point;
        $user->balance += $amount;
        $user->telegram_username = $request->username;
        $user->save();
        makeTransation(user_id: auth()->id(), trx: getTrx(), type: '+', amount: $amount, remark: 'Telegram Follow Reward');
        return new JsonResponse([
            'message' => 'Telegram Point Claim Successful',
            'telegram' => $user->telegram_username
        ]);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ApiException
     */
    public function claimFacebook(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users,facebook_username'
        ]);
        if ($request->user()->facebook_username){
            throw new ApiException("Facebook Point Already Claimed");
        }
        $user = \auth()->user();
        $amount =gs()->facebook_point;
        $user->balance += $amount;
        $user->facebook_username = $request->username;
        $user->save();
        makeTransation(user_id: auth()->id(), trx: getTrx(), type: '+', amount: $amount, remark: 'Facebook Follow Reward');
        return new JsonResponse([
            'message' => 'Facebook Point Claim Successful',
            'facebook' => $user->facebook_username
        ]);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ApiException
     */
    public function claimYoutube(Request $request)
    {
        $request->validate([
            'email' => 'required|unique:users,youtube_email|email'
        ]);
        if ($request->user()->youtube_email){
            throw new ApiException("Youtube Point Already Claimed");
        }
        $user = \auth()->user();
        $amount =gs()->youtube_point;
        $user->balance += $amount;
        $user->youtube_email = $request->email;
        $user->save();
        makeTransation(user_id: auth()->id(), trx: getTrx(), type: '+', amount: $amount, remark: 'Youtube Reward');
        return new JsonResponse([
            'message' => 'Youtube Point Claim Successful',
            'youtube_email' => $user->youtube_email
        ]);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function user(Request $request)
    {
        return new UserResource($request->user());
    }
}
