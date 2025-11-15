<?php
if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    die('Access denied. DEV_MODE required.');
}

require_once __DIR__ . '/../../core/tasks/extensionsandboxtask.php';

use core\tasks\ExtensionSandboxTask;

header('Content-Type: application/json; charset=UTF-8');

try {
    $result = ExtensionSandboxTask::run();
    echo json_encode(['task' => 'ExtensionSandboxTask', 'ok' => $result]);
} catch (\Throwable $e) {
    echo json_encode(['task' => 'ExtensionSandboxTask', 'ok' => false, 'error' => $e->getMessage()]);
}
