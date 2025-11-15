<?php

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit('Access denied');
}

require_once __DIR__ . '/../../core/tasks/logmanagertask.php';

use core\tasks\LogManagerTask;

header('Content-Type: application/json; charset=UTF-8');

$result = LogManagerTask::run();

echo json_encode([
    'task' => 'LogManagerTask',
    'ok' => $result
]);
