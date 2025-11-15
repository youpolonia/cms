<?php

require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}

require_once __DIR__ . '/../../core/tasks/translationauditortask.php';

header('Content-Type: application/json; charset=UTF-8');

$result = TranslationAuditorTask::run();

echo json_encode([
    'task' => 'TranslationAuditorTask',
    'ok' => $result
]);
