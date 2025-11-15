<?php

namespace Includes\Middleware;

use Includes\Routing\Request;
use Includes\Routing\Response;
use Includes\Auth\CSRFToken;
use Includes\Exceptions\HttpException;

class VerifyCsrfToken
{
    protected $except = [
        // Routes that should be excluded from CSRF protection
    ];

    public function handle(Request $request, callable $next): Response
    {
        if ($this->shouldPassThrough($request)) {
            return $next($request);
        }

        if (!$this->tokensMatch($request)) {
            throw new HttpException(419, 'CSRF token mismatch');
        }

        return $next($request);
    }

    protected function shouldPassThrough(Request $request): bool
    {
        foreach ($this->except as $except) {
            if ($request->is($except)) {
                return true;
            }
        }

        return in_array($request->method(), ['GET', 'HEAD', 'OPTIONS']);
    }

    protected function tokensMatch(Request $request): bool
    {
        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');
        return CSRFToken::validate($token);
    }
}
