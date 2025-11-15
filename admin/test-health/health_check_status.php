<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

header('Content-Type: application/json; charset=UTF-8');

$project_root = __DIR__ . '/../../';
$directories = ['logs', 'temp', 'sessions', 'backups', 'search_index'];

// Check each directory
$dirs = [];
$all_exist = true;
$all_writable = true;

foreach ($directories as $dir) {
    $path = $project_root . $dir;
    $exists = is_dir($path);
    $writable = $exists && is_writable($path);
    
    $dirs[$dir] = [
        'exists' => $exists,
        'writable' => $writable
    ];
    
    if (!$exists) $all_exist = false;
    if (!$writable) $all_writable = false;
}

// Check free space
$free_bytes = disk_free_space($project_root);
$free_ok = $free_bytes !== false && $free_bytes >= (100 * 1024 * 1024);

// Overall status
$overall_ok = $all_exist && $all_writable && $free_ok;

echo json_encode([
    'dirs' => $dirs,
    'free_bytes' => $free_bytes !== false ? (int)$free_bytes : 0,
    'free_ok' => $free_ok,
    'overall_ok' => $overall_ok
]);
