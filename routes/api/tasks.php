<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

Route::middleware(['auth:api'])->group(function () {
    // Public task routes
    Route::get('/', [TaskController::class, 'index']);
    Route::post('/{task}/submit', [TaskController::class, 'submit']);
    
    // Admin only routes
    Route::middleware(['auth:api'])->group(function () {
        Route::post('/', [TaskController::class, 'store']);
        Route::put('/{task}', [TaskController::class, 'update']);
        Route::put('/{task}/review', [TaskController::class, 'review']);
    });
});