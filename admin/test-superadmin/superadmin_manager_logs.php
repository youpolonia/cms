<?php

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit;
}

header('Content-Type: text/plain; charset=UTF-8');

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$limit = min($limit, 200);

$logFile = __DIR__ . '/../../logs/migrations.log';

if (!file_exists($logFile)) {
    echo "No log file found.\n";
    exit;
}

$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$filteredLines = [];

foreach ($lines as $line) {
    if (strpos($line, 'SuperAdminManagerTask') !== false) {
        $filteredLines[] = $line;
    }
}

$filteredLines = array_reverse($filteredLines);
$filteredLines = array_slice($filteredLines, 0, $limit);

foreach ($filteredLines as $line) {
    echo $line . "\n";
}
