<?php
/**
 * Workflow API Routes
 */
require_once __DIR__ . '/../includes/controllers/workflowcontroller.php';
require_once __DIR__ . '/../core/router.php';

$router = new core\Router();

// State transition endpoint
$router->post('/workflows/transition', function($request) {
    $response = WorkflowController::handleTransition(
        $request['instance_id'],
        $request['transition_name'],
        $request['context'] ?? []
    );
    return Response::json($response);
});

// Get current state endpoint
$router->get('/workflows/state', function($request) {
    $response = WorkflowController::getStatus($request['instance_id']);
    return Response::json($response);
});

// Get transition history endpoint
$router->get('/workflows/history', function($request) {
    $response = WorkflowController::getWorkflowState($request['content_id']);
    return Response::json($response);
});
