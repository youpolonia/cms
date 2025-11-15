<?php

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit('Access denied');
}

require_once __DIR__ . '/../../core/tasks/menumanagertask.php';

use core\tasks\MenuManagerTask;

header('Content-Type: application/json; charset=UTF-8');

$result = MenuManagerTask::run();

echo json_encode([
    'task' => 'MenuManagerTask',
    'ok' => $result
]);
