<?php
namespace Core;

use \Throwable;

if (class_exists(__NAMESPACE__ . '\\Router')) {
    return;
}

/**
 * Enhanced Router with:
 * - Dynamic route registration
 * - Typed route parameters
 * - Middleware support
 * - Route caching
 */
class Router {
    private static array $routes = [];
    private static array $currentMiddleware = [];
    private static string $currentPrefix = '';
    private static $notFoundHandler = null;
    private static array $routeCache = [];
    private static bool $cacheEnabled = false;
    private static array $loadedRouteFiles = [];

    // Allow instantiation
    public function __construct() {}
    
    /**
     * Load and validate route files
     * @param array $routeFiles Paths to route definition files
     * @throws \RuntimeException If route file is invalid or inaccessible
     */
    public static function load(array $routeFiles): void {
        foreach ($routeFiles as $file) {
            if (!file_exists($file)) {
                throw new \RuntimeException("Route file not found: {$file}");
            }

            if (!in_array($file, self::$loadedRouteFiles)) {
                $routeCountBefore = count(self::$routes);
                require_once $file;
                
                if (count(self::$routes) === $routeCountBefore) {
                    error_log("Warning: No routes registered in {$file}");
                }
                
                self::$loadedRouteFiles[] = $file;
            }
        }
    }

    /**
     * Add a route with HTTP method, path and handler
     */
    public static function addRoute(string $method, string $path, callable|array|string $handler, array $paramTypes = []): void {
        if (!is_callable($handler)) {
            if (is_array($handler)) {
                if (count($handler) !== 2 || !is_string($handler[0]) || !is_string($handler[1])) {
                    throw new \InvalidArgumentException('Handler array must contain exactly 2 strings [class, method]');
                }

                // Validate controller existence
                if (!class_exists('Core\\ControllerRegistry')) {
                    throw new \Exception('ControllerRegistry not found');
                }

                try {
                    // Validate controller with enhanced checks
                    if (!\Core\ControllerRegistry::validateController($handler[0], $handler[1])) {
                        error_log("Controller validation failed: {$handler[0]}::{$handler[1]}");
                        return; // Skip registration if validation fails
                    }
                    \Core\ControllerRegistry::register($handler[0], $handler[1]);
                    \Core\ControllerRegistry::logUsage($handler[0], $handler[1]);
                } catch (\Throwable $e) {
                    error_log("Controller validation error: " . $e->getMessage());
                    return; // Skip registration on validation error
                }
            } elseif (!is_string($handler)) {
                throw new \InvalidArgumentException('Handler must be callable, array [class,method] or string function name');
            }
        }
        $fullPath = self::$currentPrefix . $path;
        self::$routes[$method][$fullPath] = [
            'handler' => $handler,
            'middleware' => self::$currentMiddleware,
            'paramTypes' => $paramTypes,
            'controller' => is_array($handler) ? "{$handler[0]}::{$handler[1]}" : null
        ];
        self::clearCache();
    }

    /**
     * Set middleware for subsequent routes
     */
    public static function middleware(array $middleware): self {
        self::$currentMiddleware = $middleware;
        return new self();
    }

    /**
     * Set prefix for subsequent routes
     */
    public static function prefix(string $prefix): self {
        self::$currentPrefix = $prefix;
        return new self();
    }

    /**
     * Group routes with shared config
     */
    public static function group(callable $callback): void {
        $callback();
        self::$currentMiddleware = [];
        self::$currentPrefix = '';
    }

    /**
     * Process the current request
     */
    public static function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        // Normalize trailing slash (but keep root as /)
        if ($path !== '/' && str_ends_with($path, '/')) { $path = rtrim($path, '/'); }

        // Static asset fallback: skip dispatch for files under /uploads or with common static extensions
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (preg_match('~^/(uploads/|assets/|css/|js/).*~i', $uri) || preg_match('~\\.(png|jpg|jpeg|gif|css|js)$~i', $uri)) {
            return;
        }

        try {
            if ($route = self::findRoute($method, $path)) {
                self::executeRoute($route);
                ob_end_flush();
                return;
            }
            self::handleNotFound();
        } catch (Throwable $e) {
            ob_end_clean();
            error_log("Router dispatch failed: " . $e->getMessage());
            http_response_code(500);
            echo '500 Internal Server Error';
        }

        if (function_exists('ob_get_level') && ob_get_level() > 0) { @ob_end_flush(); }
    }

    private static function findRoute(string $method, string $path): ?array {
        $cacheKey = "$method:$path";
        
        if (self::$cacheEnabled && isset(self::$routeCache[$cacheKey])) {
            return self::$routeCache[$cacheKey];
        }

        // Debug output
        error_log("Router::findRoute - Method: $method, Path: $path");
        error_log("Available routes for $method: " . json_encode(array_keys(self::$routes[$method] ?? [])));

        foreach (self::$routes[$method] ?? [] as $routePath => $route) {
            error_log("Trying to match route: $routePath");
            $params = self::matchPath($routePath, $path, $route['paramTypes']);
            if ($params !== null) {
                error_log("Route matched: $routePath");
                $route['params'] = $params;
                
                if (self::$cacheEnabled) {
                    self::$routeCache[$cacheKey] = $route;
                }
                
                return $route;
            }
        }

        error_log("No route found for $method $path");
        return null;
    }

    private static function matchPath(string $routePath, string $path, array $paramTypes): ?array {
        // For exact match of root path
        if ($routePath === '/' && $path === '/') {
            error_log("Exact match for root path");
            return [];
        }
        
        $pattern = '#^' . preg_replace('/\{([^}]+)\}/', '(?<$1>[^/]+)', $routePath) . '$#';
        
        error_log("Matching path: '$path' against pattern: '$pattern'");
        
        if (preg_match($pattern, $path, $matches)) {
            error_log("Match found! Matches: " . json_encode($matches));
            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key) && isset($paramTypes[$key])) {
                    settype($value, $paramTypes[$key]);
                }
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }
            return $params;
        }

        error_log("No match found for pattern: $pattern");
        return null;
    }

    private static function executeRoute(array $route): void {
        try {
            foreach ($route['middleware'] as $middleware) {
                if (is_array($middleware) && count($middleware) === 2 && is_string($middleware[0]) && is_string($middleware[1])) {
                    $className = $middleware[0];
                    $methodName = $middleware[1];
                    $instance = new $className();
                    call_user_func([$instance, $methodName]);
                } else {
                    call_user_func($middleware);
                }
            }
            
            $handler = $route['handler'];
            if (is_array($handler) && count($handler) === 2 && is_string($handler[0]) && is_string($handler[1])) {
                $className = $handler[0];
                $methodName = $handler[1];
                $instance = new $className();
                call_user_func_array([$instance, $methodName], $route['params']);
            } else {
                call_user_func_array($handler, $route['params']);
            }
        } catch (Throwable $e) {
            error_log("Route execution failed: " . $e->getMessage());
            throw $e;
        }
    }

    private static function handleNotFound(): void {
        http_response_code(404);
        if (self::$notFoundHandler) {
            $handler = self::$notFoundHandler;
            if (is_array($handler) && count($handler) === 2 && is_string($handler[0]) && is_string($handler[1])) {
                $className = $handler[0];
                $methodName = $handler[1];
                $instance = new $className();
                call_user_func([$instance, $methodName]);
            } else {
                call_user_func($handler);
            }
        } else {
            echo '404 Not Found';
        }
    }

    // HTTP method shortcuts
    public static function get(string $path, callable|array|string $handler, array $paramTypes = []): void {
        self::addRoute('GET', $path, $handler, $paramTypes);
    }

    public static function post(string $path, callable|array|string $handler, array $paramTypes = []): void {
        self::addRoute('POST', $path, $handler, $paramTypes);
    }

    public static function put(string $path, callable|array|string $handler, array $paramTypes = []): void {
        self::addRoute('PUT', $path, $handler, $paramTypes);
    }

    public static function delete(string $path, callable|array|string $handler, array $paramTypes = []): void {
        self::addRoute('DELETE', $path, $handler, $paramTypes);
    }

    public static function patch(string $path, callable|array|string $handler, array $paramTypes = []): void {
        self::addRoute('PATCH', $path, $handler, $paramTypes);
    }

    // Cache control

    public static function clearCache(): void {
        self::$routeCache = [];
    }

    // Error handling
    public static function setNotFoundHandler(mixed $handler): void {
        self::$notFoundHandler = $handler;
    }
    
    // Debug helper
    public static function getRoutes(): array {
        return self::$routes;
    }
}
