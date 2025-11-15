<?php

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit;
}

require_once __DIR__ . '/../../core/tasks/adminmanagertask.php';

header('Content-Type: application/json; charset=UTF-8');

$result = AdminManagerTask::run();

echo json_encode([
    'task' => 'AdminManagerTask',
    'ok' => $result
]);
