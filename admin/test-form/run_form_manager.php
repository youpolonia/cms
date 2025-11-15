<?php

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit('Access denied');
}

require_once __DIR__ . '/../../core/tasks/formmanagertask.php';

use core\tasks\FormManagerTask;

header('Content-Type: application/json; charset=UTF-8');

$result = FormManagerTask::run();

echo json_encode([
    'task' => 'FormManagerTask',
    'ok' => $result
]);
