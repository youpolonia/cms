<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__, 2) . '/core/csrf.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
csrf_boot();

header('Content-Type: application/json; charset=UTF-8');

$logFile = __DIR__ . '/../../logs/migrations.log';
$cleared = false;

if (@file_exists($logFile)) {
    $cleared = @file_put_contents($logFile, '') !== false;
}

echo json_encode(['cleared' => $cleared]);
