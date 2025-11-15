<?php

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit('Access denied');
}

require_once __DIR__ . '/../../core/tasks/sectionmanagertask.php';

use core\tasks\SectionManagerTask;

header('Content-Type: application/json; charset=UTF-8');

$result = SectionManagerTask::run();

echo json_encode([
    'task' => 'SectionManagerTask',
    'ok' => $result
]);
