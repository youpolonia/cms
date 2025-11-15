<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../core/session_boot.php';
cms_session_start('admin');
require_once __DIR__ . '/../../core/csrf.php';
header('Content-Type: application/json; charset=UTF-8');

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}
csrf_boot();
csrf_validate_or_403();
require_once __DIR__ . '/../../core/tasks/emailqueuetask.php';

try {
    $result = EmailQueueTask::run();
    echo json_encode(['task' => 'EmailQueueTask', 'ok' => $result]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['task' => 'EmailQueueTask', 'ok' => false, 'error' => $e->getMessage()]);
}
