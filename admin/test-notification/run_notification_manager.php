<?php

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit('Access denied');
}

require_once __DIR__ . '/../../core/tasks/notificationmanagertask.php';

use core\tasks\NotificationManagerTask;

header('Content-Type: application/json; charset=UTF-8');

$result = NotificationManagerTask::run();

echo json_encode([
    'task' => 'NotificationManagerTask',
    'ok' => $result
]);
