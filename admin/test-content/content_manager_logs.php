<?php

if (file_exists(__DIR__ . '/../../config.php')) {
    require_once __DIR__ . '/../../config.php';
}

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    header('HTTP/1.1 403 Forbidden');
    die('Access denied. Development mode is not enabled.');
}

$log_file = '/var/www/html/cms/logs/migrations.log';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
if ($limit <= 0) {
    $limit = 50;
}
if ($limit > 200) {
    $limit = 200;
}

header('Content-Type: text/plain; charset=UTF-8');

if (file_exists($log_file)) {
    $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        echo "Could not read log file.";
        exit;
    }
    $filtered_lines = array_filter($lines, function($line) {
        return strpos($line, 'ContentManagerTask') !== false;
    });
    $reversed_lines = array_reverse($filtered_lines);
    $limited_lines = array_slice($reversed_lines, 0, $limit);
    echo implode("\n", $limited_lines);
} else {
    echo "Log file not found.";
}
