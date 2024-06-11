<?php

use App\Http\Controllers\Api\AuthController;

use App\Http\Controllers\MiningController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/sanctum/token', function (Request $request){
    $request->validate([
        'device_name' => 'required'
    ]);
    return $request->user()->createToken($request->device_name);
});


Route::middleware(['guest', 'api'])->group(function (){

    Route::prefix('auth')->group(function (){
        Route::post('register', [AuthController::class,'register']);
        Route::post('login', [AuthController::class,'login']);
        Route::post('forgot-password', [AuthController::class,'forgotPassword']);
        Route::post('code-verify', [AuthController::class,'codeVerify']);
        Route::post('change-password', [AuthController::class,'changePassword']);
    });

});


Route::middleware(['auth:sanctum', 'api'])->group(function (){
    Route::prefix('user')->group(function (){
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('throttle:api');
        Route::get('/update', [MiningController::class, 'getMiningInfo']);
        Route::post('/mining', [MiningController::class, 'store'])
            ->middleware(['miningAds']);
        Route::post('dailyLogin', [UserController::class, 'dailyLogin']);
        Route::post('claim/username', [UserController::class, 'claimUsername']);
        Route::post('claim/twitter', [UserController::class, 'claimTwitter']);
        Route::post('claim/telegram', [UserController::class, 'claimTelegram']);
        Route::post('claim/facebook', [UserController::class, 'claimFacebook']);
        Route::post('claim/youtube', [UserController::class, 'claimYoutube']);
        Route::post('/ads', [MiningController::class, 'ads'])
            ->middleware(['throttle:ads'])
        ;
        Route::get('/transaction', [UserController::class, 'transaction']);
        Route::post('change-password', [UserController::class,'updateUserPassword']);
        Route::post('profileUpdate', [UserController::class,'profileUpdate']);
        Route::post('/support', [UserController::class, 'store']);
        Route::post('/transfer/point', [UserController::class, 'transfer']);
        Route::get('/referral', [UserController::class, 'referral']);
        Route::get('/post/{post}', [PostController::class, 'show'])->whereNumber('post');
        Route::get('/notification', [NotificationController::class, 'index']);
        Route::post('/notification/{notification}', [NotificationController::class, 'show']);
        Route::post('/notification/read/{notification}', [NotificationController::class, 'markAsRead']);
        Route::post('/notification/read/all', [NotificationController::class, 'markAllAsRead']);
        Route::post('/notification/delete/all', [NotificationController::class, 'destroy']);
        Route::controller('App\Http\Controllers\StreakController')
            ->prefix('streak')->group(function (){
                Route::get('/', 'index');
            });
    });

});
