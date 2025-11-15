<?php
header('Content-Type: application/json');

define('CMS_ENTRY_POINT', true);
require_once __DIR__ . '/../../config.php';

try {
    $templateDir = WORKFLOW_TEMPLATE_PATH . '/';
    
    // Check if directory exists
    if (!is_dir($templateDir)) {
        throw new Exception('Templates directory not found');
    }

    // Scan directory for JSON files
    $files = array_diff(scandir($templateDir), ['.', '..']);
    $templates = array_filter($files, fn($file) => pathinfo($file, PATHINFO_EXTENSION) === 'json');

    echo json_encode(array_values($templates));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
