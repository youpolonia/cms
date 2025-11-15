<?php

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit('Access denied');
}

require_once __DIR__ . '/../../core/tasks/layoutmanagertask.php';

use core\tasks\LayoutManagerTask;

header('Content-Type: application/json; charset=UTF-8');

$result = LayoutManagerTask::run();

echo json_encode([
    'task' => 'LayoutManagerTask',
    'ok' => $result
]);
