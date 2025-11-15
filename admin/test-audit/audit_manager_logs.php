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

header('Content-Type: text/plain; charset=UTF-8');

$limit = (int)($_GET['limit'] ?? 50);
$limit = min(max($limit, 1), 200);

$log = __DIR__.'/../../logs/audit_manager.log';

if (!file_exists($log)) {
    echo "Log file not found.\n";
    exit;
}

$lines = file($log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($lines === false) {
    echo "Error reading log file.\n";
    exit;
}

$filteredLines = array_filter($lines, function($line) {
    return !empty(trim($line));
});

$filteredLines = array_reverse($filteredLines);
$limitedLines = array_slice($filteredLines, 0, $limit);

foreach ($limitedLines as $line) {
    echo $line . "\n";
}
