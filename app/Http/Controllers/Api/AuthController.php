<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\ReferralLog;
use App\Models\Streak;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use NextApps\VerificationCode\VerificationCode;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * @throws Exception
     */
    public function register(Request $request)
    {
        $input = $request->all();

        Validator::make($input, [
            'firstname' => ['bail', 'required', 'string', 'max:255'],
            'lastname' => ['bail', 'required', 'string', 'max:255'],
//            'phone' => 'bail|required|unique:users',
            'email' => [
                'bail',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'inviteCode' => 'nullable|exists:users,referral_code',
            'password' => ['required', 'confirmed', 'min:6'],
        ], [

        ])->stopOnFirstFailure(true)->validate();


        try {

            DB::transaction(function () use ($input) {
                $user = User::create([
                    'firstname' => $input['firstname'],
                    'lastname' => $input['lastname'],
                    'referral_code' => time(),
                    'balance' => 0,
//                    'phone' => $input['phone'],
                    'email' => $input['email'],
                    'password' => Hash::make($input['password']),
                ]);

                $user->save();
                if (array_key_exists('inviteCode', $input)) {
                    $upline = User::where('referral_code', $input['inviteCode'])
                        ->verified()
                        ->first();
                    if ($upline) {
                        $upline->no_of_referral += 1;
                        $upline->save();
                        $user->referral_id = $upline->id;
                        $user->save();

                    }
                    ReferralLog::create([
                        'user_id' => $upline->id,
                        'referred_id' => $user->id
                    ]);
                    $this->checkReferralLevelUp($upline, $user);
                }
            });
            return new JsonResource([
                'status' => 'success',
                'message' => 'Registration Successful'
            ], 200);

        } catch (Exception $exception) {

            throw new ApiException($exception->getMessage());
        }


    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function checkReferralLevelUp(User $upline, User $user)
    {
        $settings = gs();
        if ($upline->no_of_referral >= $settings->referral_level_no && !$upline->referral_level_up) {
            $upline->balance += $settings->referral_level_up;
            $upline->save();

            makeTransation($upline->id, getTrx(), '+', $settings->referral_level_up, 'Referral Level Up Point');
        }
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required'],
            'password' => ['required']
        ]);

        if (Auth::attempt($credentials, true)) {
            $user = \auth()->user();
//            $user->tokens()->delete();
            return new JsonResponse([
                'status' => 'success',
                'token' => Auth::user()->createToken($request->email)->plainTextToken,
                'user' => $user,
                'setting' => [
                    'referral_level_no' => gs()->referral_level_no,
                    'facebook_url' => gs()->facebook_url,
                    'twitter_url' => gs()->twitter_url,
                    'telegram_url' => gs()->telegram_url,
                    'youtube_url' => gs()->youtube_url,
                ],
                'post' => PostResource::collection(Post::all()->where('is_banner', true)),
                'ads' => PostResource::collection(Post::all()->where('is_banner', false)),
                'notification_count' => $user->notifications->count(),
                'streak_count' => $user->streak_count,
                'streak' => Streak::all()
            ]);

        }
        return new JsonResponse([
            'message' => 'User did not exit',
            'status' => 'error'
        ], 500);
    }

    public function forgotPassword(Request $request)
    {
        Validator::make($request->all(), [
            'email' => 'email|required|exists:users,email'
        ], [
            'email.exists' => 'Email Not Found'
        ])->stopOnFirstFailure(true)->validate();

        VerificationCode::send($request->email);
        return new JsonResponse([
            'status' => 'success',
            'message' => 'verification code sent'
        ]);
    }

    /**
     * @throws ApiException
     */
    public function codeVerify(Request $request)
    {
        Validator::make($request->all(), [
            'code' => 'min:4|required|max:4',
            'email' => 'email|required'
        ])->stopOnFirstFailure(true)->validate();
        $verify = VerificationCode::verify($request->code, $request->email);
        if (!$verify) {
            throw new ApiException("Code Mismatch");
        }
        return new JsonResponse([
            'message' => 'Valid Code',
            'status' => 'success',
            'hash' => encrypt($request->email)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * @throws ApiException
     */
    public function changePassword(Request $request)
    {
        Validator::make($request->all(), [
            'hash' => 'required',
            'password' => 'required|confirmed'
        ], [
            'hash.required' => 'Secret hash required'
        ])->stopOnFirstFailure(true)->validate();

        $user = User::where('email', decrypt($request->hash))->first();
        if (!$user) {
            throw new ApiException("Error try again later");
        }
        $user->password = Hash::make($request->password);
        $user->save();
        return new JsonResponse([
            'message' => 'password change successful',
            'status' => 'success'
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();


        return new JsonResponse([
            'status' => 'success', 'message' => 'Logout Successful'
        ], 200);
    }


}
