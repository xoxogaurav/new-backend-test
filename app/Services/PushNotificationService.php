<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.pushy.me/push';

    public function __construct()
    {
        $this->apiKey = config('services.pushy.api_key');
    }

    public function sendNotification(array $data, array|string $to, array $options = []): bool
    {
        try {
            if (is_array($to) && empty($to)) {
                Log::warning('No recipients provided for push notification');
                return false;
            }

            $payload = $this->buildPayload($data, $to, $options);
            $response = $this->makeRequest($payload);

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Push notification failed', [
                'error' => $e->getMessage(),
                'to' => $to,
                'data' => $data
            ]);
            throw $e;
        }
    }

    public function broadcast(array $data, array $options = []): bool
    {
        return $this->sendNotification($data, '/topics/news', $options);
    }

    protected function buildPayload(array $data, array|string $to, array $options): array
    {
        return array_merge([
            'to' => $to,
            'data' => $data
        ], $options);
    }

    protected function makeRequest(array $payload)
    {
        return Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post($this->baseUrl . '?api_key=' . $this->apiKey, $payload);
    }

    protected function handleResponse($response): bool
    {
        if (!$response->successful()) {
            throw new \Exception('Push notification request failed: ' . $response->body());
        }

        $data = $response->json();
        if (isset($data['error'])) {
            throw new \Exception('Push notification API error: ' . $data['error']);
        }

        return true;
    }
}