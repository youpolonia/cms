<?php
require_once __DIR__ . '/../../config.php';

// Immediately deny if DEV_MODE is not enabled
if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit;
}

require_once __DIR__ . '/../../core/tasks/tempcleanertask.php';

// Set JSON content type
header('Content-Type: application/json; charset=UTF-8');

// Execute the task and capture result
$result = TempCleanerTask::run();

echo json_encode([
    'task' => 'TempCleanerTask',
    'ok' => $result
]);
