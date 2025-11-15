<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

header('Content-Type: application/json; charset=UTF-8');

$index_dir = '/var/www/html/cms/search_index';

// Check if directory exists and is readable
if (!is_dir($index_dir) || !is_readable($index_dir)) {
    echo json_encode(['exists' => false, 'removed' => 0]);
    exit;
}

// Get files and count successful removals
$files = @glob($index_dir . '/*');
$removed = 0;

if ($files !== false) {
    foreach ($files as $file) {
        if (is_file($file)) {
            if (@unlink($file)) {
                $removed++;
            }
        }
    }
}

echo json_encode([
    'exists' => true,
    'removed' => $removed
]);
