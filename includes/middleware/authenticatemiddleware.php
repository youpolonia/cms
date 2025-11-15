<?php

namespace Includes\Middleware;

use Includes\Auth\Auth;

class AuthenticateMiddleware
{
    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function handle($request, $next)
    {
        if (!$this->auth->check()) {
            // User not authenticated, redirect to login
            header('Location: /login');
            exit;
        }

        return $next($request);
    }
}
