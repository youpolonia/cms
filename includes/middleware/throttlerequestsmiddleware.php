<?php

namespace Includes\Middleware;

use Includes\Auth\RateLimiter;

class ThrottleRequestsMiddleware
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle($request, $next, $maxAttempts = 60, $decayMinutes = 1)
    {
        $key = $this->resolveRequestSignature($request);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            header('HTTP/1.1 429 Too Many Requests');
            exit;
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        return $next($request);
    }

    protected function resolveRequestSignature($request)
    {
        return sha1(
            $_SERVER['REMOTE_ADDR'] . 
            $_SERVER['REQUEST_URI'] .
            ($_SERVER['HTTP_USER_AGENT'] ?? '')
        );
    }
}
