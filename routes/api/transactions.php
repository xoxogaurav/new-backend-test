<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;

Route::middleware('auth:api')->group(function () {
    Route::get('/', [TransactionController::class, 'index']);
    Route::post('/withdraw', [TransactionController::class, 'withdraw']);
});