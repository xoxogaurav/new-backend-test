<?php

use Illuminate\Support\Facades\Route;

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'service' => 'TaskFlow API'
    ]);
});

// Include route modules
Route::prefix('auth')->group(base_path('routes/api/auth.php'));
Route::prefix('users')->group(base_path('routes/api/users.php'));
Route::prefix('tasks')->group(base_path('routes/api/tasks.php'));
Route::prefix('transactions')->group(base_path('routes/api/transactions.php'));
Route::prefix('notifications')->group(base_path('routes/api/notifications.php'));
Route::prefix('admin')->group(base_path('routes/api/admin.php'));
Route::prefix('withdrawals')->group(base_path('routes/api/withdrawals.php'));
Route::prefix('referrals')->group(base_path('routes/api/referrals.php'));