<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../core/csrf.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}
csrf_boot();

require_once __DIR__ . '/../../core/tasks/migrationauditortask.php';

header('Content-Type: application/json; charset=UTF-8');

$result = MigrationAuditorTask::run();

echo json_encode([
    'task' => 'MigrationAuditorTask',
    'ok' => $result
]);
