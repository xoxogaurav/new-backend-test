<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter as CacheRateLimiter;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponse;

class RateLimiter
{
    use ApiResponse;

    protected $limiter;

    public function __construct(CacheRateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle(Request $request, Closure $next)
    {
        $key = $request->ip();
        
        if ($this->limiter->tooManyAttempts($key, 100)) {
            return $this->errorResponse('Too many requests', 'RATE_LIMIT_EXCEEDED', 429);
        }

        $this->limiter->hit($key, 60);

        return $next($request);
    }
}