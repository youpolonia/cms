<?php

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit('Access denied');
}

header('Content-Type: text/plain; charset=UTF-8');

$logFile = __DIR__ . '/../../logs/migrations.log';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$limit = min(max($limit, 1), 200);

if (!file_exists($logFile)) {
    echo "No log file found.\n";
    exit;
}

$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($lines === false) {
    echo "Error reading log file.\n";
    exit;
}

$filteredLines = array_filter($lines, function($line) {
    return strpos($line, 'DomainManagerTask') !== false;
});

$filteredLines = array_reverse($filteredLines);
$limitedLines = array_slice($filteredLines, 0, $limit);

foreach ($limitedLines as $line) {
    echo $line . "\n";
}
