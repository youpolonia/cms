<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}

header('Content-Type: application/json; charset=UTF-8');

// Include the SystemLogsManagerTask
require_once __DIR__ . '/../../core/tasks/systemlogsmanagertask.php';

try {
    $result = SystemLogsManagerTask::run();
    echo json_encode([
        'task' => 'SystemLogsManagerTask',
        'ok' => $result
    ]);
} catch (Exception $e) {
    echo json_encode(['task' => 'SystemLogsManagerTask', 'ok' => false, 'error' => $e->getMessage()]);
}
