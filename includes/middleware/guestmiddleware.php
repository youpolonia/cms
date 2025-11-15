<?php

namespace Includes\Middleware;

use Includes\Auth\Auth;

class GuestMiddleware
{
    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function handle($request, $next)
    {
        if ($this->auth->check()) {
            // User is authenticated, redirect to home
            header('Location: /');
            exit;
        }

        return $next($request);
    }
}
