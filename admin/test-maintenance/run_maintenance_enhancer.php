<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../../core/tasks/maintenanceenhancertask.php';

$result = MaintenanceEnhancerTask::run();

echo json_encode([
    'task' => 'MaintenanceEnhancerTask',
    'ok' => $result
]);
