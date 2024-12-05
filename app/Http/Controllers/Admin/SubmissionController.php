<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaskSubmission;

class SubmissionController extends Controller
{
    public function pending()
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