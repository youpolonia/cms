<?php

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit('Access denied');
}

require_once __DIR__ . '/../../core/tasks/fieldmanagertask.php';

use core\tasks\FieldManagerTask;

header('Content-Type: application/json; charset=UTF-8');

$result = FieldManagerTask::run();

echo json_encode([
    'task' => 'FieldManagerTask',
    'ok' => $result
]);
