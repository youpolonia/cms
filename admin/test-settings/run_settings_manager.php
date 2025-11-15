<?php

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit('Access denied');
}

require_once __DIR__ . '/../../core/tasks/settingsmanagertask.php';

use core\tasks\SettingsManagerTask;

header('Content-Type: application/json; charset=UTF-8');

$result = SettingsManagerTask::run();

echo json_encode([
    'task' => 'SettingsManagerTask',
    'ok' => $result
]);
