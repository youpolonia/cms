<?php

namespace Includes\Core;

class MiddlewarePipeline
{
    private array $middlewares;

    public function __construct(array $middlewares)
    {
        $this->middlewares = $middlewares;
    }

    public function handle($request, callable $next)
    {
        $middleware = array_shift($this->middlewares);

        if ($middleware) {
            $targetPath = __DIR__.'/../Middleware/'.$middleware.'.php';
            $base = realpath(__DIR__ . '/../Middleware/');
            $resolved = realpath($targetPath);
            if ($base !== false && $resolved !== false && str_starts_with($resolved, $base . DIRECTORY_SEPARATOR) && is_file($resolved)) {
                require_once $resolved;
            } else {
                http_response_code(400);
                error_log('Blocked invalid middleware path: ' . $middleware);
                return $next($request);
            }
            $middlewareInstance = new $middleware();
            return $middlewareInstance->handle($request, function($request) use ($next) {
                return $this->handle($request, $next);
            });
        }

        return $next($request);
    }
}
