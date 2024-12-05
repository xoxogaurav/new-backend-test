<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Services\PushNotificationService;
use App\Models\User;

class NotificationController extends Controller
{
    use ApiResponse;

    
    protected $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

    
    public function updateFcmToken(Request $request)
    {
        try {
            $request->validate([
                'fcm_token' => 'required|string'
            ]);

            $user = auth()->user();
            $user->update([
                'fcm_token' => $request->fcm_token
            ]);

            return $this->successResponse(null, 'FCM token updated successfully');
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 'VALIDATION_ERROR');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update FCM token', 'UPDATE_ERROR');
        }
    }
    
    
    public function index()
    {
        try {
            $notifications = Notification::where('user_id', auth()->id())
                ->orderByDesc('created_at')
                ->get();

            return $this->successResponse($notifications);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch notifications', 'FETCH_ERROR');
        }
    }

    public function markAsRead($id)
    {
        try {
            $notification = Notification::where('user_id', auth()->id())
                ->findOrFail($id);

            $notification->update(['is_read' => true]);

            return $this->successResponse(null, 'Notification marked as read');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to mark notification as read', 'UPDATE_ERROR');
        }
    }

    public function markAllAsRead()
    {
        try {
            Notification::where('user_id', auth()->id())
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return $this->successResponse(null, 'All notifications marked as read');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to mark notifications as read', 'UPDATE_ERROR');
        }
    }
}