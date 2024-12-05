<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReferralController;

Route::middleware('auth:api')->group(function () {
    Route::get('/stats', [ReferralController::class, 'getStats']);
});