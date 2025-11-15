<?php
if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    die('Access denied. DEV_MODE required.');
}

require_once __DIR__ . '/../../core/tasks/searchrebuildertask.php';

use core\tasks\SearchRebuilderTask;

header('Content-Type: application/json; charset=UTF-8');

try {
    $result = SearchRebuilderTask::run();
    echo json_encode(['task' => 'SearchRebuilderTask', 'ok' => $result]);
} catch (\Throwable $e) {
    echo json_encode(['task' => 'SearchRebuilderTask', 'ok' => false, 'error' => $e->getMessage()]);
}
