<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

    public function sendNotifications(array $userIds, string $title, string $message, array $additionalData = [])
    {
        try {
            // Get users with FCM tokens
            $users = User::whereIn('id', $userIds)
                ->get();

            if ($users->isEmpty()) {
                throw new \Exception('No users found');
            }

            // Create local notifications
            $this->createLocalNotifications($users, $title, $message);

            // Send push notifications to users with FCM tokens
            $tokens = $users->whereNotNull('fcm_token')
                ->pluck('fcm_token')
                ->toArray();

            if (!empty($tokens)) {
                $this->sendPushNotifications($tokens, $title, $message, $additionalData);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send notifications', [
                'error' => $e->getMessage(),
                'user_ids' => $userIds
            ]);
            throw $e;
        }
    }

    protected function createLocalNotifications($users, string $title, string $message)
    {
        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'type' => 'info',
                'is_read' => false
            ]);
        }
    }

    protected function sendPushNotifications(array $tokens, string $title, string $message, array $additionalData)
    {
        $data = array_merge([
            'title' => $title,
            'message' => $message
        ], $additionalData);

        $options = [
            'notification' => [
                'title' => $title,
                'body' => $message,
                'badge' => 1,
                'sound' => 'default'
            ]
        ];

        return $this->pushService->sendNotification($data, $tokens, $options);
    }
}