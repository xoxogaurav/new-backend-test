<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Validation\ValidationException;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, ApiResponse;

    /**
     * Success response method.
     *
     * @param mixed $data
     * @param string|null $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success($data = null, string $message = null, int $code = 200)
    {
        return $this->successResponse($data, $message, $code);
    }

    /**
     * Error response method.
     *
     * @param string $message
     * @param string $code
     * @param int $httpCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error(string $message, string $code, int $httpCode = 400)
    {
        return $this->errorResponse($message, $code, $httpCode);
    }

    /**
     * Validation error response method.
     *
     * @param array $errors
     * @return \Illuminate\Http\JsonResponse
     */
    protected function validationError(array $errors)
    {
        return $this->errorResponse(
            'The given data was invalid.',
            'VALIDATION_ERROR',
            422,
        );
    }

    /**
     * Not found error response method.
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function notFound(string $message = 'Resource not found')
    {
        return $this->errorResponse($message, 'NOT_FOUND', 404);
    }

    /**
     * Unauthorized error response method.
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function unauthorized(string $message = 'Unauthorized')
    {
        return $this->errorResponse($message, 'UNAUTHORIZED', 401);
    }

    /**
     * Forbidden error response method.
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function forbidden(string $message = 'Forbidden')
    {
        return $this->errorResponse($message, 'FORBIDDEN', 403);
    }
}