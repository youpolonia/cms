<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../../core/tasks/searchindextask.php';

$result = SearchIndexTask::run();

echo json_encode([
    'task' => 'SearchIndexTask',
    'ok' => $result
]);
