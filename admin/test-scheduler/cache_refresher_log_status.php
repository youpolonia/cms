<?php
require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__, 2) . '/core/csrf.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
csrf_boot();

// Detect if being included or accessed directly
$isIncluded = (count(get_included_files()) > 1);

if (!$isIncluded) {
    header('Content-Type: application/json; charset=UTF-8');
}

$logFile = __DIR__ . '/../../logs/migrations.log';

if (!@file_exists($logFile)) {
    echo json_encode(['exists' => false]);
    if (!$isIncluded) {
        exit;
    }
    return;
}

$sizeBytes = @filesize($logFile);
$lines = @file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$lineCount = $lines !== false ? count($lines) : 0;

$skippedLast = false;
if ($lines !== false && $lineCount > 0) {
    $lastLine = end($lines);
    $skippedLast = strpos($lastLine, 'skipped') !== false;
}

echo json_encode([
    'exists' => true,
    'size_bytes' => (int)$sizeBytes,
    'line_count' => $lineCount,
    'skipped_last' => $skippedLast
]);
