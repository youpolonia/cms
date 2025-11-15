<?php

/**
 * Register global middleware for the CMS
 */

// Include middleware classes
require_once __DIR__ . '/../middleware/userbehaviortrackingmiddleware.php';
require_once __DIR__ . '/../middleware/tenantisolationmiddleware.php';

use Includes\Middleware\UserBehaviorTrackingMiddleware;
use Includes\Middleware\TenantIsolationMiddleware;

/**
 * Register middleware with the router
 *
 * @param \Includes\RoutingV2\Router $router
 * @return void
 */
function registerMiddleware($router) {
    // Register global middleware
    $router->addGlobalMiddleware(new UserBehaviorTrackingMiddleware());
    $router->addGlobalMiddleware(new TenantIsolationMiddleware());
    
    // Add other global middleware here
}
