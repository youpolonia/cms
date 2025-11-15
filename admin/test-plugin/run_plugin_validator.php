<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}

require_once __DIR__ . '/../../core/tasks/pluginvalidatortask.php';

header('Content-Type: application/json; charset=UTF-8');

try {
    $result = PluginValidatorTask::run();
    echo json_encode(['task' => 'PluginValidatorTask', 'ok' => $result]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['task' => 'PluginValidatorTask', 'ok' => false, 'error' => $e->getMessage()]);
}
