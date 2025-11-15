<?php
use api\v1\Controllers\WorkflowController;

$router->group('/api/v1/workflows', function($router) {
    // Create new workflow
    $router->post('', function($request) {
        return WorkflowController::createWorkflow($request);
    });

    // Update workflow
    $router->put('/{workflow_id}', function($request, $params) {
        $request['workflow_id'] = $params['workflow_id'];
        return WorkflowController::updateWorkflow($request);
    });

    // Execute workflow
    $router->post('/{workflow_id}/execute', function($request, $params) {
        $request['workflow_id'] = $params['workflow_id'];
        return WorkflowController::executeWorkflow($request);
    });

    // Get workflow status
    $router->get('/{workflow_id}/status', function($request, $params) {
        $request['workflow_id'] = $params['workflow_id'];
        return WorkflowController::getWorkflowStatus($request);
    });

    // List all workflows
    $router->get('', function($request) {
        return WorkflowController::listWorkflows($request);
    });
});

// Workflow triggers management
$router->group('/api/v1/workflows/{workflow_id}/triggers', function($router) {
    $router->post('', function($request, $params) {
        // TODO: Implement trigger management
    });

    $router->delete('/{trigger_id}', function($request, $params) {
        // TODO: Implement trigger deletion
    });
});

// Workflow actions management
$router->group('/api/v1/workflows/{workflow_id}/actions', function($router) {
    $router->post('', function($request, $params) {
        // TODO: Implement action management
    });

    $router->delete('/{action_id}', function($request, $params) {
        // TODO: Implement action deletion
    });
});

// Status transitions management
$router->group('/api/v1/workflows/transitions', function($router) {
    // Apply state transition
    $router->post('', function($request) {
        $request['tenant_id'] = $request->headers['X-Tenant-Context'] ?? null;
        return (new WorkflowController())->transition($request);
    });

    // Get current state
    $router->get('/state', function($request) {
        $request['tenant_id'] = $request->headers['X-Tenant-Context'] ?? null;
        return (new WorkflowController())->getState($request);
    });

    // Get transition history
    $router->get('/history', function($request) {
        $request['tenant_id'] = $request->headers['X-Tenant-Context'] ?? null;
        return (new WorkflowController())->getHistory($request);
    });
});
