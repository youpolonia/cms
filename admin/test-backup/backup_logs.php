<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

header('Content-Type: text/plain; charset=UTF-8');

$log_file = __DIR__ . '/../../logs/migrations.log';

// Check if log file exists
if (!file_exists($log_file)) {
    exit;
}

// Get limit parameter
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$limit = max(1, min($limit, 200)); // Clamp between 1 and 200

// Read and filter log lines
$lines = @file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($lines === false) {
    exit;
}

// Filter lines containing "BackupTask"
$backup_lines = array_filter($lines, function($line) {
    return strpos($line, 'BackupTask') !== false;
});

// Get last N lines and reverse chronological order
$backup_lines = array_slice($backup_lines, -$limit);
$backup_lines = array_reverse($backup_lines);

// Output the lines
foreach ($backup_lines as $line) {
    echo $line . PHP_EOL;
}
