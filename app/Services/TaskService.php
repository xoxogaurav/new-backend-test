<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskSubmission;
use App\Models\Transaction;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TaskService
{
    public function submitTask(Task $task, string $screenshotUrl)
    {
        return DB::transaction(function () use ($task, $screenshotUrl) {
            $submission = TaskSubmission::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'screenshot_url' => $screenshotUrl,
                'status' => $task->approval_type === 'automatic' ? 'approved' : 'pending',
            ]);

            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'task_id' => $task->id,
                'amount' => $task->reward,
                'type' => 'earning',
                'status' => $task->approval_type === 'automatic' ? 'completed' : 'pending',
            ]);

            $user = auth()->user();

            if ($task->approval_type === 'automatic') {
                $this->handleAutomaticApproval($user, $task);
            } else {
                $this->handlePendingSubmission($user, $task);
            }

            return ['submission' => $submission, 'transaction' => $transaction];
        });
    }

    public function reviewSubmission(Task $task, int $submissionId, string $status)
    {
        return DB::transaction(function () use ($task, $submissionId, $status) {
            $submission = TaskSubmission::findOrFail($submissionId);
            $submission->status = $status;
            $submission->save();

            $transaction = Transaction::where('task_id', $task->id)
                ->where('user_id', $submission->user_id)
                ->first();

            $user = User::findOrFail($submission->user_id);

            if ($status === 'approved') {
                $user->balance += $task->reward;
                $user->tasks_completed += 1;
                $user->pending_earnings -= $task->reward;
                $user->save();

                if ($transaction) {
                    $transaction->status = 'completed';
                    $transaction->save();
                }

                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Task Approved',
                    'message' => "Your submission for \"{$task->title}\" has been approved! \${$task->reward} has been added to your balance.",
                    'type' => 'success',
                    'is_read' => false,
                ]);
            } else {
                $user->pending_earnings -= $task->reward;
                $user->save();

                if ($transaction) {
                    $transaction->status = 'failed';
                    $transaction->save();
                }

                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Task Rejected',
                    'message' => "Your submission for \"{$task->title}\" has been rejected. Please review the requirements and try again.",
                    'type' => 'error',
                    'is_read' => false,
                ]);
            }

            return $submission;
        });
    }

    private function handleAutomaticApproval($user, $task)
    {
        $user->balance += $task->reward;
        $user->tasks_completed += 1;
        $user->save();

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Task Completed',
            'message' => "Your submission for \"{$task->title}\" has been automatically approved! \${$task->reward} has been added to your balance.",
            'type' => 'success',
            'is_read' => false,
        ]);
    }

    private function handlePendingSubmission($user, $task)
    {
        $user->pending_earnings += $task->reward;
        $user->save();

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Task Submitted',
            'message' => "Your submission for \"{$task->title}\" is pending review. We'll notify you once it's approved.",
            'type' => 'info',
            'is_read' => false,
        ]);
    }
}