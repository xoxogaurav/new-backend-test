<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Task;
use App\Models\TaskSubmission;
use App\Models\Transaction;

class StatsController extends Controller
{
    public function index()
    {
        $stats = [
            'users' => User::count(),
            'tasks' => Task::count(),
            'pendingSubmissions' => TaskSubmission::where('status', 'pending')->count(),
            'totalEarnings' => Transaction::where('type', 'earning')->sum('amount'),
            'recentActivity' => [
                'newUsers' => User::where('created_at', '>=', now()->subDays(7))->count(),
                'completedTasks' => TaskSubmission::where('status', 'approved')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count(),
                'totalRevenue' => Transaction::where('type', 'earning')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->sum('amount')
            ]
        ];

        return $this->successResponse($stats);
    }
}