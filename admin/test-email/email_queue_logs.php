<?php
require_once __DIR__ . '/../../config.php';

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}

header('Content-Type: text/plain; charset=UTF-8');

$logFile = __DIR__ . '/../../logs/email_queue.log';
$limit = isset($_GET['limit']) ? min(max((int)$_GET['limit'], 1), 200) : 50;

if (!file_exists($logFile)) {
    echo "No log file found.\n";
    exit;
}

$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($lines === false) {
    echo "Error reading log file.\n";
    exit;
}

$filtered = array_filter($lines, function($line) {
    return strpos($line, 'EmailQueueTask') !== false;
});

// Get last N entries (reverse chronological)
$recent = array_slice(array_reverse($filtered), 0, $limit);

echo implode("\n", $recent) . "\n";
