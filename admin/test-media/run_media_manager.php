<?php

// This is a simplified bootstrap for dev tools
if (file_exists(__DIR__ . '/../../config.php')) {
    require_once __DIR__ . '/../../config.php';
}

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    header('HTTP/1.1 403 Forbidden');
    die('Access denied. Development mode is not enabled.');
}

require_once __DIR__ . '/../../core/tasks/mediamanagertask.php';

$result = MediaManagerTask::run();

header('Content-Type: application/json; charset=UTF-8');
echo json_encode([
    'task' => 'MediaManagerTask',
    'ok' => $result
]);
