<?php

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit('Access denied');
}

require_once __DIR__ . '/../../core/tasks/blockmanagertask.php';

use core\tasks\BlockManagerTask;

header('Content-Type: application/json; charset=UTF-8');

$result = BlockManagerTask::run();

echo json_encode([
    'task' => 'BlockManagerTask',
    'ok' => $result
]);
