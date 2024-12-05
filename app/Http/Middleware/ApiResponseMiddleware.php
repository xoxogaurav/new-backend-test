<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response instanceof Response && $response->headers->get('Content-Type') === 'application/json') {
            $content = json_decode($response->getContent(), true);

            if (!isset($content['success'])) {
                $content = [
                    'success' => $response->isSuccessful(),
                    'data' => $content
                ];

                if ($response->getStatusCode() >= 400) {
                    $content['error'] = [
                        'message' => $content['data']['message'] ?? 'An error occurred',
                        'code' => $content['data']['code'] ?? 'ERROR'
                    ];
                    unset($content['data']);
                }

                $response->setContent(json_encode($content));
            }
        }

        return $response;
    }
}