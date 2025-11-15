<?php

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit('Access denied');
}

require_once __DIR__ . '/../../core/tasks/alertmanagertask.php';

use core\tasks\AlertManagerTask;

header('Content-Type: application/json; charset=UTF-8');

$result = AlertManagerTask::run();

echo json_encode([
    'task' => 'AlertManagerTask',
    'ok' => $result
]);
