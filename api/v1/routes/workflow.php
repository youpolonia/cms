<?php
use api\v1\Controllers\WorkflowController;
use Middleware\AuthMiddleware;

// Status transitions endpoints
$router->post('/api/workflow/status-transitions', function($request) {
    // Verify admin access
    if (!AuthMiddleware::isAdmin()) {
        return [
            'success' => false,
            'error' => 'Unauthorized',
            'code' => 401
        ];
    }
    return WorkflowController::recordStatusTransition($request);
});

$router->get('/api/workflow/status-transitions', function($request) {
    // Verify authenticated access
    if (!AuthMiddleware::isAuthenticated()) {
        return [
            'success' => false,
            'error' => 'Unauthorized',
            'code' => 401
        ];
    }
    return WorkflowController::getStatusHistory($request);
});

// Existing workflow routes would be here...
