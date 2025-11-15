<?php
require_once __DIR__ . '/../../config.php';

// Immediately deny if DEV_MODE is not enabled
if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit;
}

// Set plain text content type
header('Content-Type: text/plain; charset=UTF-8');

$log_file = 'logs/migrations.log';

// Check if log file exists
if (!file_exists($log_file) || !is_readable($log_file)) {
    exit;
}

// Get limit parameter with bounds checking
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$limit = max(1, min(200, $limit));

// Read log file and filter for TempCleanerTask entries
$lines = @file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($lines === false) {
    exit;
}

// Filter lines containing TempCleanerTask
$temp_lines = [];
foreach ($lines as $line) {
    if (strpos($line, 'TempCleanerTask') !== false) {
        $temp_lines[] = $line;
    }
}

// Get last N lines in reverse chronological order
$temp_lines = array_reverse(array_slice(array_reverse($temp_lines), 0, $limit));

// Output the filtered lines
foreach ($temp_lines as $line) {
    echo $line . PHP_EOL;
}
