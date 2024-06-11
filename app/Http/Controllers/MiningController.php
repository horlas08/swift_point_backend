<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMiningRequest;
use App\Http\Requests\UpdateMiningRequest;
use App\Http\Resources\PostResource;
use App\Models\Mining;
use App\Models\Post;
use App\Models\Streak;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class MiningController extends Controller
{


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }
    private static function handleStreakCount(User $user)
    {
        if (Carbon::parse($user->last_mining)->isYesterday()){
            $user->streak_count += 1;
        }else{
            $user->streak_count = 1;
        }
        $user->last_mining = now();
        $user->save();
        $streak = Streak::where('days', $user->streak_count)->first();
        $lastStreak = Streak::latest('days');
        if ($streak){
            $user->balance += $streak->point;
            makeTransation(user_id: $user->id, trx: getTrx(), type: '+', amount: $streak->point, remark: "Streak Point For $streak->days days");
            if ($lastStreak->is($streak)){
                $user->streak_count = 0;
                $user->save();
            }
        }


    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMiningRequest $request)
    {
        $user = $request->user();
        $amount = gs()->mining;

        try {
            $user->balance += $amount;

        } catch (NotFoundExceptionInterface|ContainerExceptionInterface| \Exception $e) {
            return response()->json([
                'message' => 'Mining Error try again later',
                'amount' => $amount
            ], 500);
        }
      self::handleStreakCount($user);

        makeTransation($user->id, getTrx(), '+', $amount, 'Mining Successful');
        return response()->json([
            'message' => 'SP Mining Successful',
            'data' => [
                'balance' => $user->balance,
                'last_mining' => $user->last_mining,
                'streak_count' => $user->streak_count
            ],
            'amount' => $amount
        ], 200);
    }
 /**
     * Store a newly created resource in storage.
     */
    public function ads(StoreMiningRequest $request)
    {
        $user = $request->user();
        $amount = gs()->ads;
        try {
            $user->balance += $amount;
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface|\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
        $user->save();
        makeTransation($user->id, getTrx(), '+', $amount, 'Ads Point Mint');
        return response()->json([
            'message' => 'Ads Reward Added',
            'amount' => $amount
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function getMiningInfo(Mining $mining)
    {
        $user = auth()->user();
        return new JsonResponse([
            'balance' => $user->balance,
            'last_mining' => $user->last_mining,
            'no_of_referral' => $user->no_of_referral,
            'test' => Carbon::parse(now()->toDateTimeString()),
            'image' => $user->image,
            'second' => now()->diffInSeconds($user->last_mining),
            'banner' => PostResource::collection(Post::all()->where('is_banner', true))->collection,
            'notification_count' =>  $user->messages->where('read', false)->count(),
            'streak_count' =>  $user->streak_count,
            'streak' =>  Streak::all()

        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMiningRequest $request, Mining $mining)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mining $mining)
    {
        //
    }
}
