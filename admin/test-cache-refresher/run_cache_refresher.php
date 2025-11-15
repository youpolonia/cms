<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}

header('Content-Type: application/json; charset=UTF-8');

// Include the CacheRefresherTask
require_once __DIR__ . '/../../core/tasks/cacherefreshertask.php';

try {
    $result = CacheRefresherTask::run();
    echo json_encode([
        'task' => 'CacheRefresherTask',
        'ok' => $result
    ]);
} catch (Exception $e) {
    echo json_encode(['task' => 'CacheRefresherTask', 'ok' => false, 'error' => $e->getMessage()]);
}
