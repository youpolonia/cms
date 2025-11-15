<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}

header('Content-Type: application/json; charset=UTF-8');

// Include the BackupAuditorTask
require_once __DIR__ . '/../../core/tasks/backupauditortask.php';

try {
    $result = BackupAuditorTask::run();
    echo json_encode([
        'task' => 'BackupAuditorTask',
        'ok' => $result
    ]);
} catch (Exception $e) {
    echo json_encode(['task' => 'BackupAuditorTask', 'ok' => false, 'error' => $e->getMessage()]);
}
