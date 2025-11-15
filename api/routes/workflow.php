<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/controllers/workflowcontroller.php';

use Includes\Controllers\WorkflowController;

$workflowController = new WorkflowController();

// Get workflow status
$router->get('/api/v1/workflow/:instanceId/status', function($instanceId) use ($workflowController) {
    header('Content-Type: application/json');
    echo json_encode($workflowController->getStatus($instanceId));
});

// Execute workflow transition
$router->post('/api/v1/workflow/:instanceId/transition/:transitionName', function($instanceId, $transitionName) use ($workflowController) {
    $context = json_decode(file_get_contents('php://input'), true) ?? [];
    header('Content-Type: application/json');
    echo json_encode($workflowController->handleTransition($instanceId, $transitionName, $context));
});

// Cancel workflow
$router->post('/api/v1/workflow/:instanceId/cancel', function($instanceId) use ($workflowController) {
    header('Content-Type: application/json');
    echo json_encode($workflowController->cancel($instanceId));
});
