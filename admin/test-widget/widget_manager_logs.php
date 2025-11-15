<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

header('Content-Type: text/plain; charset=UTF-8');

$limit = (int)($_GET['limit'] ?? 50);
$limit = min(max($limit, 1), 200);

$logFile = '../../logs/migrations.log';

if (!file_exists($logFile)) {
    echo "Log file not found.\n";
    exit;
}

$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($lines === false) {
    echo "Error reading log file.\n";
    exit;
}

$filteredLines = array_filter($lines, function($line) {
    return strpos($line, 'WidgetManagerTask') !== false;
});

$filteredLines = array_reverse($filteredLines);
$limitedLines = array_slice($filteredLines, 0, $limit);

foreach ($limitedLines as $line) {
    echo $line . "\n";
}
