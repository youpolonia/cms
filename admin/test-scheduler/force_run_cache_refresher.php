<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__, 2) . '/core/csrf.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
csrf_boot();

require_once __DIR__ . '/../../core/tasks/cacherefreshertask.php';

header('Content-Type: application/json; charset=UTF-8');

$result = \core\tasks\CacheRefresherTask::run(true);

echo json_encode([
    'forced' => true,
    'ok' => $result
]);
