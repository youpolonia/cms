<?php

require_once __DIR__.'/../../config.php';

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    echo 'Access denied';
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo 'Method Not Allowed';
    exit;
}

require_once __DIR__ . '/../../core/tasks/auditmanagertask.php';

$result = AuditManagerTask::run();

header('Content-Type: application/json; charset=UTF-8');
echo '{"task":"AuditManagerTask","ok":true}';
