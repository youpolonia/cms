<?php
require_once __DIR__ . '/../../../core/workflowexecutor.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed', 405);
    }

    require_once __DIR__ . '/../../../core/csrf.php';
    csrf_validate_or_403();

    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }

    if (empty($input['workflow_id'])) {
        throw new Exception('Workflow ID is required');
    }

    $executor = new WorkflowExecutor($input['workflow_id'], $input['input_vars'] ?? []);
    $result = $executor->execute();

    http_response_code(200);
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
