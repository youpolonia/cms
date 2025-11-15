<?php

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit;
}

require_once __DIR__ . '/../../core/tasks/preferencemanagertask.php';

header('Content-Type: application/json; charset=UTF-8');

$result = PreferenceManagerTask::run();

echo json_encode([
    'task' => 'PreferenceManagerTask',
    'ok' => $result
]);
