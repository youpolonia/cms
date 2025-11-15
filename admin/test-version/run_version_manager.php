<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}

header('Content-Type: application/json; charset=UTF-8');

// Include the VersionManagerTask
require_once __DIR__ . '/../../core/tasks/versionmanagertask.php';

try {
    $result = VersionManagerTask::run();
    echo json_encode([
        'task' => 'VersionManagerTask',
        'ok' => $result
    ]);
} catch (Exception $e) {
    echo json_encode(['task' => 'VersionManagerTask', 'ok' => false, 'error' => $e->getMessage()]);
}
