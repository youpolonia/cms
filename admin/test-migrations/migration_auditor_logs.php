<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../core/csrf.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}
csrf_boot();

header('Content-Type: text/plain; charset=UTF-8');

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
if ($limit > 200) {
    $limit = 200;
}
if ($limit < 1) {
    $limit = 1;
}

$logFile = __DIR__ . '/../../logs/migrations.log';

if (!file_exists($logFile)) {
    echo "No log file found.\n";
    exit;
}

$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($lines === false) {
    echo "Error reading log file.\n";
    exit;
}

$filteredLines = [];
foreach ($lines as $line) {
    if (strpos($line, 'MigrationAuditorTask') !== false) {
        $filteredLines[] = $line;
    }
}

$filteredLines = array_reverse($filteredLines);
$limitedLines = array_slice($filteredLines, 0, $limit);

foreach ($limitedLines as $line) {
    echo $line . "\n";
}
