<?php
if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    die('Access denied. DEV_MODE required.');
}

require_once __DIR__ . '/../../core/tasks/sessioncleanertask.php';

use core\tasks\SessionCleanerTask;

header('Content-Type: application/json; charset=UTF-8');

try {
    $result = SessionCleanerTask::run();
    echo json_encode(['task' => 'SessionCleanerTask', 'ok' => $result]);
} catch (\Throwable $e) {
    echo json_encode(['task' => 'SessionCleanerTask', 'ok' => false, 'error' => $e->getMessage()]);
}
