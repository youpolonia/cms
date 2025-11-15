<?php

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit;
}

require_once __DIR__ . '/../../core/tasks/accountmanagertask.php';

header('Content-Type: application/json; charset=UTF-8');

$result = AccountManagerTask::run();

echo json_encode([
    'task' => 'AccountManagerTask',
    'ok' => $result
]);
