<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\StatsController;
use App\Http\Controllers\Admin\SubmissionController;
use App\Http\Controllers\Admin\WithdrawalController;
use App\Http\Controllers\Admin\NotificationController;

Route::middleware(['auth:api'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/stats', [StatsController::class, 'index']);
    Route::get('/submissions/pending', [SubmissionController::class, 'pending']);
    Route::put('/{userId}/verify-id', [UserController::class, 'approveGovtId']);
    Route::get('/users/pending-verifications', [UserController::class, 'getPendingGovtIdUsers']);
    
    // Withdrawal routes
    Route::get('/withdrawals/pending', [WithdrawalController::class, 'pending']);
    Route::put('/withdrawals/{withdrawal}/process', [WithdrawalController::class, 'approve']);
    
    //send notification
    Route::post('/notifications/send', [NotificationController::class, 'send']);
    Route::post('/notifications/broadcast', [NotificationController::class, 'broadcast']);
});