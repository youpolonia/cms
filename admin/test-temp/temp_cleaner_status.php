<?php
require_once __DIR__ . '/../../config.php';

// Immediately deny if DEV_MODE is not enabled
if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit;
}

// Set JSON content type
header('Content-Type: application/json; charset=UTF-8');

$temp_dir = '/var/www/html/cms/temp';

// Check if directory exists
if (!is_dir($temp_dir)) {
    echo json_encode(['exists' => false]);
    exit;
}

// Directory exists, gather statistics
$file_count = 0;
$total_size = 0;

$files = @glob($temp_dir . '/*');
if ($files !== false) {
    $file_count = count($files);
    foreach ($files as $file) {
        if (is_file($file)) {
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
