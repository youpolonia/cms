<?php
require_once __DIR__ . '/../../config.php';

// Immediately deny if DEV_MODE is not enabled
if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit;
}

// Set JSON content type
header('Content-Type: application/json; charset=UTF-8');

$sessions_dir = '/var/www/html/cms/sessions';

// Check if directory exists and is readable
if (!is_dir($sessions_dir) || !is_readable($sessions_dir)) {
    echo json_encode(['exists' => false, 'removed' => 0]);
    exit;
}

// Directory exists, remove files
$removed_count = 0;
$files = @glob($sessions_dir . '/*');

if ($files !== false) {
    foreach ($files as $file) {
        if (is_file($file)) {
            if (@unlink($file)) {
                $removed_count++;
            }
        }
    }
}

echo json_encode([
    'exists' => true,
    'removed' => $removed_count
]);
