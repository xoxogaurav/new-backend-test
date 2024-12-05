<?php

namespace App\Http\Traits;

trait ApiResponse
{
    protected function successResponse($data, $message = null, $code = 200)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message
        ], $code);
    }

    protected function errorResponse($message, $code, $httpCode = 400)
    {
        return response()->json([
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $code
            ]
        ], $httpCode);
    }
}