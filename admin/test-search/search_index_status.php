<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

header('Content-Type: application/json; charset=UTF-8');

$index_dir = '/var/www/html/cms/search_index';

// Check if directory exists
if (!is_dir($index_dir)) {
    echo json_encode(['exists' => false]);
    exit;
}

// Count files and calculate total size
$files = @glob($index_dir . '/*');
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
