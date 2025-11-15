<?php

class AuthModule {
    public static function init() {
        // Register routes
        $routes = require __DIR__ . '/routes.php';
        foreach ($routes as $route => $handler) {
            list($method, $path) = explode(' ', $route, 2);
            \core\Router::addRoute($method, $path, $handler);
        }
    }
}
