<?php
require_once __DIR__ . '/../core/bootstrap.php';
try {
    // Load route files before dispatch
    if (class_exists('\\Core\\Router')) {
        $routeFiles = [
            __DIR__ . '/../routes.php'
        ];
        // Guarded optional load of custom web routes (DEV only)
        if (defined('DEV_MODE') && DEV_MODE === true) {
            $p = __DIR__ . '/../routes_custom/web.php';
            if (is_file($p)) { $routeFiles[] = $p; }
        }
        foreach ($routeFiles as $file) {
            if (file_exists($file)) {
                \Core\Router::load([$file]);
            }
        }
    }

    if (function_exists('router_dispatch')) {
        router_dispatch($_SERVER['REQUEST_URI'] ?? '/');
        return;
    }
    if (class_exists('\\core\\router')) {
        \core\router::dispatch($_SERVER['REQUEST_URI'] ?? '/');
        return;
    }
    if (class_exists('\\Core\\Router')) {
        \Core\Router::dispatch($_SERVER['REQUEST_URI'] ?? '/');
        return;
    }
    if (file_exists(__DIR__ . '/index.view.php')) {
        require_once __DIR__ . '/index.view.php';
        return;
    }
    header('Content-Type: text/plain; charset=UTF-8');
    echo "OK";
} catch (\Throwable $e) {
    http_response_code(500);
    error_log('[public/index] '.$e->getMessage());
    echo 'Error';
}
