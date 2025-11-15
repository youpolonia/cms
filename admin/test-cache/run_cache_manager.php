<?php

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit('Access denied');
}

require_once __DIR__ . '/../../core/tasks/cachemanagertask.php';

use core\tasks\CacheManagerTask;

header('Content-Type: application/json; charset=UTF-8');

$result = CacheManagerTask::run();

echo json_encode([
    'task' => 'CacheManagerTask',
    'ok' => $result
]);
