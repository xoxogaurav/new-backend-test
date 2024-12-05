<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WithdrawalController;

Route::middleware('auth:api')->group(function () {
    Route::get('/', [WithdrawalController::class, 'index']);
    Route::post('/', [WithdrawalController::class, 'store']);
});