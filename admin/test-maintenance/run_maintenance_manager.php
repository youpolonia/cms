<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}

header('Content-Type: application/json; charset=UTF-8');

// Include the MaintenanceManagerTask
require_once __DIR__ . '/../../core/tasks/maintenancemanagertask.php';

try {
    $result = MaintenanceManagerTask::run();
    echo json_encode([
        'task' => 'MaintenanceManagerTask',
        'ok' => $result
    ]);
} catch (Exception $e) {
    echo json_encode(['task' => 'MaintenanceManagerTask', 'ok' => false, 'error' => $e->getMessage()]);
}
