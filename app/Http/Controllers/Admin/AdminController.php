<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Task;
use App\Models\TaskSubmission;
use App\Models\Transaction;

class AdminController extends Controller
{
    public function users()
    {
        $users = User::select([
            'id', 'name', 'email', 'balance', 'pending_earnings',
            'total_withdrawn', 'tasks_completed', 'success_rate',
            'average_rating', 'created_at'
        ])->get();

        return $this->successResponse($users);
    }

    public function stats()
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

    public function pendingSubmissions()
    {
        $submissions = TaskSubmission::with(['user:id,name,email', 'task:id,title,reward'])
            ->where('status', 'pending')
            ->get()
            ->map(function ($submission) {
                return [
                    'id' => $submission->id,
                    'taskId' => $submission->task_id,
                    'userId' => $submission->user_id,
                    'status' => $submission->status,
                    'screenshotUrl' => $submission->screenshot_url,
                    'submittedAt' => $submission->created_at,
                    'user' => [
                        'name' => $submission->user->name,
                        'email' => $submission->user->email
                    ],
                    'task' => [
                        'title' => $submission->task->title,
                        'reward' => $submission->task->reward
                    ]
                ];
            });

        return $this->successResponse($submissions);
    }
}