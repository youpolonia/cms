<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}

header('Content-Type: text/plain; charset=UTF-8');

$logFile = __DIR__ . '/../../logs/migrations.log';
$limit = min(200, max(1, (int)($_GET['limit'] ?? 50)));

if (!file_exists($logFile)) {
    echo "No logs found.\n";
    exit;
}

$content = file_get_contents($logFile);
$lines = explode("\n", $content);

$filteredLines = array_filter($lines, function($line) {
    return strpos($line, 'SecurityScannerTask') !== false;
});

$filteredLines = array_reverse($filteredLines);
$filteredLines = array_slice($filteredLines, 0, $limit);

foreach ($filteredLines as $line) {
    echo $line . "\n";
}
