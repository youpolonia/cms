<?php

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}

header('Content-Type: text/plain; charset=UTF-8');

$log_file = '/var/www/html/cms/logs/migrations.log';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
if ($limit > 200) {
    $limit = 200;
}

if (!file_exists($log_file)) {
    echo "Log file not found.";
    exit;
}

$lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$filtered_lines = array_filter($lines, function($line) {
    return strpos($line, 'NotificationAuditorTask') !== false;
});

$reversed_lines = array_reverse($filtered_lines);
$limited_lines = array_slice($reversed_lines, 0, $limit);

echo implode("\n", $limited_lines);
