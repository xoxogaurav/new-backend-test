<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\TaskService;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    use ApiResponse;

    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index()
    {
        try {
            $user = auth()->user();
            
            // Return all tasks for admin users
            if ($user->is_admin) {
                $tasks = Task::all();
                return $this->successResponse($tasks);
            }

            // Filter tasks for regular users
            $tasks = Task::where('is_active', true)
                ->get()
                ->filter(function ($task) use ($user) {
                    return $task->isAvailableForUser($user);
                })
                ->values();

            return $this->successResponse($tasks);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch tasks', 'FETCH_ERROR');
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'reward' => 'required|numeric|min:0',
                'time_estimate' => 'required|string',
                'category' => 'required|string',
                'difficulty' => 'required|in:Easy,Medium,Hard',
                'time_in_seconds' => 'required|integer|min:0',
                'steps' => 'required|array',
                'approval_type' => 'required|in:automatic,manual',
                'allowed_countries' => 'nullable|array',
                'allowed_countries.*' => 'string|size:2',
                'hourly_limit' => 'integer|min:0',
                'daily_limit' => 'integer|min:0',
                'one_off' => 'boolean',
                'total_submission_limit' => 'integer|min:0',
                'daily_submission_limit' => 'integer|min:0'
                
            ]);

            $task = Task::create([
                'title' => $request->title,
                'description' => $request->description,
                'reward' => $request->reward,
                'time_estimate' => $request->time_estimate,
                'category' => $request->category,
                'difficulty' => $request->difficulty,
                'time_in_seconds' => $request->time_in_seconds,
                'steps' => $request->steps,
                'approval_type' => $request->approval_type,
                'allowed_countries' => $request->allowed_countries,
                'hourly_limit' => $request->hourly_limit ?? 0,
                'daily_limit' => $request->daily_limit ?? 0,
                'one_off' => $request->one_off ?? false,
                'total_submission_limit' => $request->total_submission_limit,
                'daily_submission_limit' => $request->daily_submission_limit

            ]);

            return $this->successResponse($task, 'Task created successfully', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 'VALIDATION_ERROR');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create task', 'CREATE_ERROR');
        }
    }

    public function update(Request $request, Task $task)
    {
        try {
            $request->validate([
                'title' => 'string|max:255',
                'description' => 'string',
                'reward' => 'numeric|min:0',
                'time_estimate' => 'string',
                'category' => 'string',
                'difficulty' => 'in:Easy,Medium,Hard',
                'time_in_seconds' => 'integer|min:0',
                'steps' => 'array',
                'approval_type' => 'in:automatic,manual',
                'is_active' => 'boolean',
                'allowed_countries' => 'nullable|array',
                'allowed_countries.*' => 'string|size:2',
                'hourly_limit' => 'integer|min:0',
                'daily_limit' => 'integer|min:0',
                'one_off' => 'boolean',
                'total_submission_limit' => 'integer|min:0',
                'daily_submission_limit' => 'integer|min:0'
            ]);

            $updateData = $request->only([
                'title',
                'description',
                'reward',
                'time_estimate',
                'category',
                'difficulty',
                'time_in_seconds',
                'steps',
                'approval_type',
                'is_active',
                'allowed_countries',
                'hourly_limit',
                'daily_limit',
                'one_off',
                'total_submission_limit',
                'daily_submission_limit'
            ]);

            $task->update($updateData);

            return $this->successResponse($task, 'Task updated successfully');
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 'VALIDATION_ERROR');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update task', 'UPDATE_ERROR');
        }
    }

    public function submit(Request $request, Task $task)
    {
        try {
            $request->validate([
                'screenshot_url' => 'required|url',
            ]);

            if (!$task->isAvailableForUser(auth()->user())) {
                return $this->errorResponse('Task is not available for submission', 'TASK_NOT_AVAILABLE');
            }

            $result = $this->taskService->submitTask($task, $request->screenshot_url);
            return $this->successResponse($result, 'Task submitted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to submit task', 'SUBMISSION_ERROR');
        }
    }

    public function review(Request $request, Task $task)
    {
        try {
            $request->validate([
                'submission_id' => 'required',
                'status' => 'required|in:approved,rejected',
            ]);

            $result = $this->taskService->reviewSubmission(
                $task,
                $request->submission_id,
                $request->status
            );
            return $this->successResponse($result, 'Review submitted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to review submission', 'REVIEW_ERROR');
        }
    }
}