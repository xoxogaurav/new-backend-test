<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::middleware('auth:api')->group(function () {
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::put('/id-verify', [UserController::class, 'uploadGovtId']);
    Route::get('/leaderboard', [UserController::class, 'leaderboard']);
    Route::get('/referrals', [UserController::class, 'getReferrals']);
});