<?php
require_once __DIR__ . '/../../config.php';

// DEV_MODE guard
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}

// Set JSON header
header('Content-Type: application/json; charset=UTF-8');

// Require the task class
require_once __DIR__ . '/../../core/tasks/backupvalidatortask.php';

try {
    $result = BackupValidatorTask::run();
    echo json_encode(['task' => 'BackupValidatorTask', 'ok' => $result]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['task' => 'BackupValidatorTask', 'ok' => false, 'error' => $e->getMessage()]);
}
