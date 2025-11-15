<?php
declare(strict_types=1);

// Include dependencies
require_once __DIR__ . '/../includes/database/connection.php';
require_once __DIR__ . '/../../core/router.php';
require_once __DIR__ . '/../includes/api/authentication.php';
require_once __DIR__ . '/../auth/middleware/workerauthenticate.php';
require_once __DIR__ . '/../../includes/services/TenantFeatures.php';
require_once __DIR__ . '/controllers/workercontroller.php';
require_once __DIR__ . '/controllers/workflowcontroller.php';
require_once __DIR__ . '/../includes/workflow/WorkflowService.php';
require_once __DIR__ . '/../includes/http/response.php'; // Added for CMS\Http\Response

// Create middleware instance
$workerAuth = new WorkerAuthenticate();

// Get instances
$router = new RoutingV2\Router();
$db = \core\Database::connection();
$auth = new CMS\API\Authentication();

// Worker management routes
$router->post('/workers/register', function($request) use ($db, $auth, $workerAuth) {
    $workerAuth->process($request);
    try {
        $auth->requirePermission('worker_management');
        $controller = new WorkerController($db);
        return $controller->registerWorker($request->getParsedBody());
    } catch (\RuntimeException $e) {
        return new \CMS\Http\Response(
            ['error' => $e->getMessage()],
            $e->getCode() ?: 500
        );
    }
});

$router->post('/workers/heartbeat', function($request) use ($db, $auth, $workerAuth) {
    $workerAuth->process($request);
    try {
        $auth->requirePermission('worker_management');
        $controller = new WorkerController($db);
        return $controller->processHeartbeat($request->getParsedBody());
    } catch (\RuntimeException $e) {
        return new \CMS\Http\Response(
            ['error' => $e->getMessage()],
            $e->getCode() ?: 500
        );
    }
});

$router->get('/workers/metrics', function($request) use ($db, $auth, $workerAuth) {
    $workerAuth->process($request);
    try {
        $auth->requirePermission('worker_management');
        $controller = new WorkerController($db);
        return $controller->getWorkerMetrics();
    } catch (\RuntimeException $e) {
        return new \CMS\Http\Response(
            ['error' => $e->getMessage()],
            $e->getCode() ?: 500
        );
    }
});

$router->get('/workers/scaling', function($request) use ($db, $auth, $workerAuth) {
    $workerAuth->process($request);
    try {
        $auth->requirePermission('worker_management');
        $controller = new WorkerController($db);
        return $controller->getScalingRecommendations();
    } catch (\RuntimeException $e) {
        return new \CMS\Http\Response(
            ['error' => $e->getMessage()],
            $e->getCode() ?: 500
        );
    }
});

$router->get('/workers/active', function($request) use ($db, $auth, $workerAuth) {
    $workerAuth->process($request);
    try {
        $auth->requirePermission('worker_management');
        $controller = new WorkerController($db);
        return $controller->getActiveWorkers();
    } catch (\RuntimeException $e) {
        return new \CMS\Http\Response(
            ['error' => $e->getMessage()],
            $e->getCode() ?: 500
        );
    }
});

// Workflow monitoring routes
$router->get('/workflows', function($request) {
    $tenantId = $request->getHeader('X-Tenant-ID')[0] ?? '';
    if (!TenantFeatures::isEnabled($tenantId, 'workflow_monitoring')) {
        return new \CMS\Http\Response(
            ['error' => 'Workflow monitoring feature not enabled for this tenant'],
            403
        );
    }
    $service = new WorkflowService();
    $controller = new WorkflowController($service);
    return $controller->listWorkflows($request, new Response());
});

// Tenant-specific analytics route
$router->get('/analytics', function($request) {
    $tenantId = $request->getHeader('X-Tenant-ID')[0] ?? '';
    if (!TenantFeatures::isEnabled($tenantId, 'analytics_dashboard')) {
        return new \CMS\Http\Response(
            ['error' => 'Analytics dashboard feature not enabled for this tenant'],
            403
        );
    }
    // Analytics implementation would go here
    return new \CMS\Http\Response(['status' => 'Analytics data']);
});

$router->get('/workflows/{id}', function($request, $id) {
    $service = new WorkflowService();
    $controller = new WorkflowController($service);
    return $controller->getWorkflowDetails($request, new Response(), $id);
});

$router->get('/workflows/{id}/status', function($request, $id) use ($auth) {
    try {
        $auth->requirePermission('workflow_monitoring');
        $service = new WorkflowService();
        $controller = new WorkflowController($service);
        return $controller->getWorkflowStatus($request, new Response(), $id);
    } catch (\RuntimeException $e) {
        return new \CMS\Http\Response(
            ['error' => $e->getMessage()],
            $e->getCode() ?: 500
        );
    }
});

// Webhook routes
$router->post('/webhooks/n8n', function($request) use ($auth) {
    try {
        $auth->requirePermission('webhook_processing');
        $handler = new \CMS\API\Webhooks\WebhookHandler();
        return $handler->processN8nWebhook($request);
    } catch (\RuntimeException $e) {
        return new \CMS\Http\Response(
            ['error' => $e->getMessage()],
            $e->getCode() ?: 500
        );
    }
});

return $router;
// Approval Workflow Routes
add_route('POST', '/api/v1/approval/submit', 'ApprovalWorkflowController@submitForApproval', [
    'middleware' => ['auth', 'CheckPermission:content_submit']
]);
add_route('GET', '/api/v1/approval/{id}', 'ApprovalWorkflowController@getApprovalStatus', [
    'middleware' => ['auth', 'CheckPermission:content_view']
]);
add_route('PUT', '/api/v1/approval/{id}/approve', 'ApprovalWorkflowController@approveContent', [
    'middleware' => ['auth', 'CheckPermission:content_approve']
]);
add_route('PUT', '/api/v1/approval/{id}/reject', 'ApprovalWorkflowController@rejectContent', [
    'middleware' => ['auth', 'CheckPermission:content_approve']
]);
add_route('GET', '/api/v1/approval/history/{contentId}', 'ApprovalWorkflowController@getApprovalHistory', [
    'middleware' => ['auth', 'CheckPermission:content_view']
]);
