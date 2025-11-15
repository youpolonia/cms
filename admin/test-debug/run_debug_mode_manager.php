<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

header('Content-Type: application/json; charset=UTF-8');

// Include the DebugModeManagerTask
require_once __DIR__ . '/../../core/tasks/debugmodemanagertask.php';

try {
    $result = DebugModeManagerTask::run();
    echo json_encode([
        'task' => 'DebugModeManagerTask',
        'ok' => $result
    ]);
} catch (Exception $e) {
    echo json_encode(['task' => 'DebugModeManagerTask', 'ok' => false, 'error' => $e->getMessage()]);
}
