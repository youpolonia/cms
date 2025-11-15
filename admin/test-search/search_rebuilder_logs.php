<?php
if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    die('Access denied. DEV_MODE required.');
}

header('Content-Type: text/plain; charset=UTF-8');

$logFile = __DIR__ . '/../../logs/migrations.log';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$limit = max(1, min($limit, 200)); // Enforce min 1, max 200

if (!file_exists($logFile)) {
    echo "No log file found.\n";
    exit;
}

$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($lines === false) {
    echo "Error reading log file.\n";
    exit;
}

// Filter lines containing "SearchRebuilderTask"
$filteredLines = array_filter($lines, function($line) {
    return strpos($line, 'SearchRebuilderTask') !== false;
});

// Get last $limit entries in reverse chronological order
$recentLines = array_slice(array_reverse($filteredLines), 0, $limit);

foreach ($recentLines as $line) {
    echo $line . "\n";
}
