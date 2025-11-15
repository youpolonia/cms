<?php

require_once __DIR__ . '/../../config.php';

if (!defined('DEV_MODE') || !DEV_MODE) {
    die('Development mode is not enabled.');
}

require_once __DIR__ . '/../../core/tasks/profilemanagertask.php';

$result = ProfileManagerTask::run();

header('Content-Type: application/json; charset=UTF-8');
echo json_encode(['task' => 'ProfileManagerTask', 'ok' => $result]);
