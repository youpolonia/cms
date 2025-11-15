<?php

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit('Access denied');
}

require_once __DIR__ . '/../../core/tasks/accessmanagertask.php';

header('Content-Type: application/json; charset=UTF-8');

try {
    $result = AccessManagerTask::run();
    echo json_encode([
        'task' => 'AccessManagerTask',
        'ok' => $result
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'task' => 'AccessManagerTask',
        'ok' => false,
        'error' => $e->getMessage()
    ]);
}
