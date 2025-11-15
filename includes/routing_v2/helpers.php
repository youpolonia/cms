<?php

require_once __DIR__ . '/../core/router.php'; // For \CMS\Routing\Router
require_once __DIR__ . '/../Core/Route.php';  // For \Includes\Core\Route

if (!function_exists('routeGroup')) {
    /**
     * Create a route group with shared attributes
     *
     * @param string $prefix
     * @param callable $routes
     * @param array $middleware
     * @return void
     */
    function routeGroup(string $prefix, callable $routes, array $middleware = []) {
        $router = \CMS\Routing\Router::getInstance();
        
        // Store current context
        $currentPrefix = $router->getCurrentPrefix();
        $currentMiddleware = $router->getCurrentMiddleware();
        
        // Apply new context
        $router->setCurrentPrefix($currentPrefix . $prefix);
        $router->setCurrentMiddleware(array_merge($currentMiddleware, $middleware));
        
        // Execute routes
        $routes();
        
        // Restore previous context
        $router->setCurrentPrefix($currentPrefix);
        $router->setCurrentMiddleware($currentMiddleware);
    }
}

if (!function_exists('route')) {
    /**
     * Register a new route
     *
     * @param string $method
     * @param string $uri
     * @param mixed $action
     * @return \Includes\Core\Route
     */
    function route(string $method, string $uri, $action) {
        return \CMS\Routing\Router::getInstance()
            ->addRoute($method, $uri, $action);
    }
}

if (!function_exists('get')) {
    function get(string $uri, $action) {
        return route('GET', $uri, $action);
    }
}

if (!function_exists('post')) {
    function post(string $uri, $action) {
        return route('POST', $uri, $action);
    }
}
