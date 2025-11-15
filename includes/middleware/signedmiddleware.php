<?php

namespace Includes\Middleware;

use Includes\Auth\Auth;

class SignedMiddleware
{
    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function handle($request, $next)
    {
        if (!isset($request['signature']) || !isset($request['user'])) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }

        if (!$this->auth->verifyEmail((int)$request['user'], (string)$request['signature'])) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }

        return $next($request);
    }
}
