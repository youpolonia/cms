<?php

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}

require_once __DIR__ . '/../../core/tasks/notificationauditortask.php';

$result = NotificationAuditorTask::run();

header('Content-Type: application/json; charset=UTF-8');
echo json_encode([
    'task' => 'NotificationAuditorTask',
    'ok' => $result
]);
