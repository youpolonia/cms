<?php

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit('Access denied');
}

require_once __DIR__ . '/../../core/tasks/navigatormanagertask.php';

use core\tasks\NavigatorManagerTask;

header('Content-Type: application/json; charset=UTF-8');

$result = NavigatorManagerTask::run();

echo json_encode([
    'task' => 'NavigatorManagerTask',
    'ok' => $result
]);
