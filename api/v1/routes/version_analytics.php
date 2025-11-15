<?php
// Version analytics API routes

use Api\v1\Controllers\VersionController;
use Includes\RoutingV2\Middleware\CheckPermission;

return function($router) {
    // Track version event
    $router->post('/api/v1/versions/{id}/track', [VersionController::class, 'trackEvent'])
        ->middleware(new CheckPermission('track_versions'));
    
    // Get version analytics
    $router->get('/api/v1/versions/{id}/analytics', [VersionController::class, 'getAnalytics'])
        ->middleware(new CheckPermission('view_version_analytics'));

    // Temporary redirect for backward compatibility
    $router->get('/versions/{id}/analytics', function($request, $response, $args) {
        return $response
            ->withHeader('Location', '/api/v1/versions/' . $args['id'] . '/analytics')
            ->withStatus(301);
    });
};
