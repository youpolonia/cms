<?php

require_once __DIR__ . '/../../config.php';

if (!defined('DEV_MODE') || !DEV_MODE) {
    die('Development mode is not enabled.');
}

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
if ($limit > 200) {
    $limit = 200;
}

$log_file = '/var/www/html/cms/logs/migrations.log';

if (!file_exists($log_file)) {
    echo "Log file not found.";
    exit;
}

$lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$filtered_lines = array_filter($lines, function($line) {
    return strpos($line, 'ProfileManagerTask') !== false;
});

$reversed_lines = array_reverse($filtered_lines);
$limited_lines = array_slice($reversed_lines, 0, $limit);

header('Content-Type: text/plain; charset=UTF-8');
echo implode("\n", $limited_lines);
