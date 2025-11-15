<?php

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit('Access denied');
}

require_once __DIR__ . '/../../core/tasks/inputmanagertask.php';

use core\tasks\InputManagerTask;

header('Content-Type: application/json; charset=UTF-8');

$result = InputManagerTask::run();

echo json_encode([
    'task' => 'InputManagerTask',
    'ok' => $result
]);
