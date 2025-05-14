<?php
/**
 * CMS Entry Point
 *
 * Initializes routing and handles requests
 */

declare(strict_types=1);

// Initialize the router
require_once __DIR__ . '/../includes/routing/Router.php';
require_once __DIR__ . '/../includes/routing/Request.php';
require_once __DIR__ . '/../includes/routing/Response.php';
require_once __DIR__ . '/../includes/config/middleware.php';

try {
    // Create and configure router instance
    $router = new Router();
    
    // Register middleware
    registerMiddleware($router);
    
    // Load web routes
    require_once __DIR__ . '/../routes/web.php';
    
    // Create request from globals
    $request = new Request($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
    
    // Handle the current request
    $response = $router->handle($request);
    $response->send();
} catch (RouterException $e) {
    // Handle routing-specific errors
    http_response_code($e->getCode());
    echo $e->getMessage();
} catch (Throwable $e) {
    // General error handling
    error_log($e->getMessage());
    http_response_code(500);
    echo "An error occurred while processing your request";
}
