<?php

class Router {
    private static $routes = [];
    private static $middleware = [];
    private static $prefix = '';

    public static function addRoute(string $method, string $path, $handler, array $middleware = []) {
        $path = self::$prefix . $path;
        $middleware = array_merge(self::$middleware, $middleware);
        
        self::$routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    public static function group(array $options, callable $callback) {
        $previousPrefix = self::$prefix;
        $previousMiddleware = self::$middleware;

        if (isset($options['prefix'])) {
            self::$prefix .= $options['prefix'];
        }

        if (isset($options['middleware'])) {
            self::$middleware = array_merge(self::$middleware, (array)$options['middleware']);
        }

        $callback();

        self::$prefix = $previousPrefix;
        self::$middleware = $previousMiddleware;
    }

    public static function dispatch() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach (self::$routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            $pattern = '#^' . preg_replace('/\{[^}]+\}/', '([^/]+)', $route['path']) . '$#';
            if (preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches);

                // Apply middleware
                foreach ($route['middleware'] as $middleware) {
                    $middlewareClass = $middleware . 'Middleware';
                    if (class_exists($middlewareClass)) {
                        (new $middlewareClass())->handle();
                    }
                }

                // Call handler
                if (is_callable($route['handler'])) {
                    return call_user_func_array($route['handler'], $matches);
                } elseif (is_string($route['handler']) && strpos($route['handler'], '@') !== false) {
                    list($controller, $method) = explode('@', $route['handler']);
                    $controllerClass = $controller . 'Controller';
                    if (class_exists($controllerClass)) {
                        return call_user_func_array([new $controllerClass(), $method], $matches);
                    }
                }

                return false;
            }
        }

        http_response_code(404);
        echo '404 Not Found';
    }
}
