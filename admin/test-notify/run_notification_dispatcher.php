<?php
require_once __DIR__ . '/../../config.php';

// DEV_MODE guard
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}

// Set JSON header
header('Content-Type: application/json; charset=UTF-8');

// Require the task class
require_once __DIR__ . '/../../core/tasks/notificationdispatchertask.php';

try {
    $result = NotificationDispatcherTask::run();
    echo json_encode(['task' => 'NotificationDispatcherTask', 'ok' => $result]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['task' => 'NotificationDispatcherTask', 'ok' => false, 'error' => $e->getMessage()]);
}
