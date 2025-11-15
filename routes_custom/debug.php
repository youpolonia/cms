<?php

if (!defined('DEV_MODE')) {
    http_response_code(500);
    echo 'Configuration error';
    return;
}
if (!DEV_MODE) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Forbidden in production";
    return;
}

// Load environment helper
require_once __DIR__ . '/../includes/helpers/env.php';

// Debug routes - only available in development environment
if (DEV_MODE) {
    $router->group(['middleware' => 'admin'], function($router) {
        // Session inspection route
        $router->get('/debug/session', function() {
            header('Content-Type: application/json');
            echo json_encode($_SESSION, JSON_PRETTY_PRINT);
            exit;
        });

        // Config inspection route
        $router->get('/debug/config', function() {
            header('Content-Type: application/json');
            $configs = [
                'app' => config('app'),
                'database' => config('database'),
                'session' => config('session')
            ];
            echo json_encode($configs, JSON_PRETTY_PRINT);
            exit;
        });

        // Route list inspection
        $router->get('/debug/routes', function() use ($router) {
            header('Content-Type: application/json');
            $routes = [];
            foreach ($router->getRoutes() as $route) {
                $routes[] = [
                    'method' => $route->getMethod(),
                    'uri' => $route->getUri(),
                    'action' => $route->getAction()
                ];
            }
            echo json_encode($routes, JSON_PRETTY_PRINT);
            exit;
        });
    });
}
