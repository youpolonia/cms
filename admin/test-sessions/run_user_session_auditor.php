<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../core/tasks/usersessionauditortask.php';

// DEV_MODE guard
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}

// Set JSON header
header('Content-Type: application/json; charset=UTF-8');

try {
    $result = UserSessionAuditorTask::run();
    echo json_encode([
        'success' => $result,
        'message' => $result ? 'User session auditor task completed successfully' : 'User session auditor task completed with no changes (placeholder)'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error running user session auditor task: ' . $e->getMessage()
    ]);
}
