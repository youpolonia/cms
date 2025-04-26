<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Closure;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        'mcp/*'
    ];

    public function handle($request, Closure $next)
    {
        if ($request->is('mcp/*')) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}