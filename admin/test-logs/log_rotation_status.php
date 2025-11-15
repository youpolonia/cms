<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { 
    http_response_code(403); 
    exit; 
}

header('Content-Type: application/json; charset=UTF-8');

$logFiles = [
    'app_errors' => __DIR__ . '/../../logs/app_errors.log',
    'php_errors' => __DIR__ . '/../../logs/php_errors.log'
];

$status = [];

foreach ($logFiles as $key => $logFile) {
    if (@file_exists($logFile)) {
        $sizeBytes = @filesize($logFile) ?: 0;
        $lines = @file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lineCount = $lines !== false ? count($lines) : 0;
        
        $status[$key] = [
            'exists' => true,
            'size_bytes' => (int)$sizeBytes,
            'line_count' => $lineCount
        ];
    } else {
        $status[$key] = [
            'exists' => false,
            'size_bytes' => 0,
            'line_count' => 0
        ];
    }
}

echo json_encode($status);
