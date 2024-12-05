<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Services\NotificationService;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Services\PushNotificationService;


class NotificationController extends Controller
{
    use ApiResponse;

    protected $notificationService;
    protected $pushService;

    public function __construct(NotificationService $notificationService, PushNotificationService $pushService)
    {
        $this->notificationService = $notificationService;
        $this->pushService = $pushService;
    }

    
    
    public function send(Request $request)
    {
        try {
            $request->validate([
                'user_ids' => 'required|array',
                'user_ids.*' => 'exists:users,id',
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'data' => 'nullable|array'
            ]);

            $this->notificationService->sendNotifications(
                $request->user_ids,
                $request->title,
                $request->message,
                $request->data ?? []
            );

            return $this->successResponse(null, 'Notifications sent successfully');
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 'VALIDATION_ERROR');
        } catch (\Exception $e) {
            Log::error('Failed to send notifications', [
                'error' => $e->getMessage(),
                'user_ids' => $request->user_ids ?? []
            ]);
            return $this->errorResponse($e->getMessage(), 'NOTIFICATION_ERROR');
        }
    }

     public function broadcast(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'url' => 'nullable|url',
                'image' => 'nullable|url'
            ]);

            $data = [
                'title' => $request->title,
                'message' => $request->message
            ];

            if ($request->url) {
                $data['url'] = $request->url;
            }

            if ($request->image) {
                $data['image'] = $request->image;
            }

            $options = [
                'notification' => [
                    'title' => $request->title,
                    'body' => $request->message,
                    'badge' => 1,
                    'sound' => 'default',
                    'icon' => $request->image ?? null
                ]
            ];

            $this->pushService->broadcast($data, $options);

            return $this->successResponse(null, 'Broadcast notification sent successfully');
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 'VALIDATION_ERROR');
        } catch (\Exception $e) {
            Log::error('Failed to send broadcast notification', [
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse($e->getMessage(), 'NOTIFICATION_ERROR');
        }
    }
}