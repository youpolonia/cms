<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once __DIR__ . '/../../core/tasks/widgetmanagertask.php';

use core\tasks\WidgetManagerTask;

header('Content-Type: application/json; charset=UTF-8');

$result = WidgetManagerTask::run();

echo json_encode([
    'task' => 'WidgetManagerTask',
    'ok' => $result
]);
