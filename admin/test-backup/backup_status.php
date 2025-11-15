<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

header('Content-Type: application/json; charset=UTF-8');

$backup_dir = '/var/www/html/cms/backups';

// Check if directory exists
if (!is_dir($backup_dir)) {
    echo json_encode(['exists' => false]);
    exit;
}

// Count files and calculate total size
$files = @glob($backup_dir . '/*');
$file_count = 0;
$total_size = 0;

if ($files !== false) {
    foreach ($files as $file) {
        if (is_file($file)) {
            $file_count++;
            $size = @filesize($file);
            if ($size !== false) {
                $total_size += $size;
            }
        }
    }
}

echo json_encode([
    'exists' => true,
    'file_count' => $file_count,
    'total_size' => $total_size
]);
