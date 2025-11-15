<?php

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit;
}

require_once __DIR__ . '/../../core/tasks/ownermanagertask.php';

header('Content-Type: application/json; charset=UTF-8');

$result = OwnerManagerTask::run();

echo json_encode([
    'task' => 'OwnerManagerTask',
    'ok' => $result
]);
