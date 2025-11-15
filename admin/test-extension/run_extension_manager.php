<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}

header('Content-Type: application/json; charset=UTF-8');

// Include the ExtensionManagerTask
require_once __DIR__ . '/../../core/tasks/extensionmanagertask.php';

try {
    $result = ExtensionManagerTask::run();
    echo json_encode([
        'task' => 'ExtensionManagerTask',
        'ok' => $result
    ]);
} catch (Exception $e) {
    echo json_encode(['task' => 'ExtensionManagerTask', 'ok' => false, 'error' => $e->getMessage()]);
}
