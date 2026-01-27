<?php
require_once __DIR__.'/../../services/workflowapicontroller.php';
require_once __DIR__.'/../../services/promptchainengine.php';
require_once __DIR__ . '/../../includes/core/auth.php';

// Initialize services
$auth = new AuthService();
$storage = new WorkflowStorage();
$engine = new PromptChainEngine(
    new CloudAIService(),
    $storage,
    new AuditLogger(),
    new MemoryBank()
);
$controller = new WorkflowApiController($engine, $storage, $auth);

// Handle request
try {
    $request = json_decode(file_get_contents('php://input'), true);
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    switch ($method) {
        case 'POST':
            require_once __DIR__ . '/../../core/csrf.php';
            csrf_validate_or_403();

            if (strpos($path, '/execute') !== false) {
                $response = $controller->executeWorkflow($request);
            } else {
                $response = $controller->saveWorkflow($request);
            }
            break;
        case 'GET':
            if (strpos($path, '/status') !== false) {
                $response = $controller->getStatus($request);
            } else {
                $response = $controller->getWorkflow($request);
            }
            break;
        default:
            throw new Exception('Method not allowed');
    }

    header('Content-Type: application/json');
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
