<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { 
    http_response_code(403); 
    exit; 
}

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

// Filter only SessionCleanerTask lines
$filteredLines = [];
foreach ($lines as $line) {
    if (strpos($line, 'SessionCleanerTask') !== false) {
        $filteredLines[] = $line;
    }
}

$filteredLines = array_slice($filteredLines, -$limit);
$filteredLines = array_reverse($filteredLines);

foreach ($filteredLines as $line) {
    echo $line . PHP_EOL;
}
