<?php
declare(strict_types=1);

namespace Api\Gateway;

require_once __DIR__ . '/middleware/authmiddleware.php';
require_once __DIR__ . '/middleware/validationmiddleware.php';

use Api\Gateway\Middleware\AuthMiddleware;
use Api\Gateway\Middleware\ValidationMiddleware;

class Router
{
    public static function handle(array $request): array
    {
        // Middleware pipeline
        $middlewareStack = [
            ValidationMiddleware::class,
            AuthMiddleware::class,
            [self::class, 'routeRequest']
        ];

        return self::processMiddlewareStack($request, $middlewareStack);
    }

    private static function processMiddlewareStack(array $request, array $stack): array
    {
        $next = array_shift($stack);
        
        if (empty($stack)) {
            return is_callable($next) 
                ? $next($request)
                : call_user_func($next, $request, fn() => []);
        }

        return is_callable($next)
            ? $next($request, fn($req) => self::processMiddlewareStack($req, $stack))
            : call_user_func($next, $request, fn($req) => self::processMiddlewareStack($req, $stack));
    }

    private static function routeRequest(array $request): array
    {
        // TODO: Implement actual routing logic
        return [
            'status' => 404,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode(['error' => 'Not Found'])
        ];
    }
}
