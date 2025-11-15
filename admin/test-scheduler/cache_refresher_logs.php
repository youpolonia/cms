<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__, 2) . '/core/csrf.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
csrf_boot();

header('Content-Type: text/plain; charset=UTF-8');

$logFile = __DIR__ . '/../../logs/migrations.log';
if (!file_exists($logFile)) {
    exit;
}

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$limit = max(1, min(200, $limit));

$lines = @file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($lines === false) {
    exit;
}

$lines = array_slice($lines, -$limit);
$lines = array_reverse($lines);

foreach ($lines as $line) {
    echo $line . PHP_EOL;
}
