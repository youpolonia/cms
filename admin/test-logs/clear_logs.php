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

$results = [];

foreach ($logFiles as $key => $logFile) {
    $cleared = false;
    if (@file_exists($logFile)) {
        $cleared = @file_put_contents($logFile, '') !== false;
    }
    $results[$key] = $cleared;
}

echo json_encode($results);
