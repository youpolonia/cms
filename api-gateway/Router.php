<?php
/**
 * Enhanced API Gateway Router
 * 
 * Responsibilities:
 * - Route incoming requests with parameter support
 * - Handle middleware execution pipeline
 * - Support API versioning
 */
class Router {
    private static $routes = [];
    private static $middlewareStack = [];

    /**
     * Add a route with middleware support
     */
    public static function addRoute(
        string $method, 
        string $path, 
        callable $handler,
        array $middlewares = []
    ): void {
        $pattern = self::convertPathToRegex($path);
        self::$routes[$method][$pattern] = [
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    /**
     * Add global middleware
     */
    public static function addMiddleware(callable $middleware): void {
        self::$middlewareStack[] = $middleware;
    }

    /**
     * Handle incoming request with middleware pipeline
     */
    public static function handleRequest(array $request, PDO $pdo): array {
        $method = $request['method'];
        $path = $request['path'];
        $version = $request['headers']['Accept-Version'] ?? '1.0';

        // Find matching route
        $route = self::findMatchingRoute($method, $path);
        if (!$route) {
            return [
                'status' => 404,
                'body' => ['error' => 'Route not found']
            ];
        }

        // Create middleware pipeline
        $pipeline = array_merge(
            self::$middlewareStack,
            $route['middlewares'],
            [$route['handler']]
        );

        // Execute pipeline
        return self::executePipeline($pipeline, $request, $pdo);
    }

    private static function convertPathToRegex(string $path): string {
        return preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
    }

    private static function findMatchingRoute(string $method, string $path): ?array {
        if (!isset(self::$routes[$method])) {
            return null;
        }

        foreach (self::$routes[$method] as $pattern => $route) {
            if (preg_match("#^$pattern$#", $path, $matches)) {
                return [
                    'handler' => $route['handler'],
                    'middlewares' => $route['middlewares'],
                    'params' => array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY)
                ];
            }
        }

        return null;
    }

    private static function executePipeline(array $pipeline, array $request, PDO $pdo): array {
        $next = function($request) use ($pdo, &$next, &$pipeline) {
            if (empty($pipeline)) {
                return [
                    'status' => 500,
                    'body' => ['error' => 'Empty middleware pipeline']
                ];
            }

            $current = array_shift($pipeline);
            return $current($request, $pdo, $next);
        };

        return $next($request);
    }
}
