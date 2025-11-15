<?php
require_once __DIR__ . '/../../core/accesschecker.php';
require_once __DIR__ . '/../../core/csrf.php';

// Verify admin permissions
if (!AccessChecker::hasPermission('media.manage')) {
    http_response_code(403);
    die('Access denied');
}

// Get and validate input
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['path']) || !isset($data['token']) || !CSRF::validate($data['token'])) {
    http_response_code(400);
    die('Invalid request');
}

// Validate path is under media directory
$basePath = realpath(__DIR__ . '/../../media/');
$targetPath = realpath(__DIR__ . '/../../' . $data['path']);

if (!$targetPath || strpos($targetPath, $basePath) !== 0) {
    http_response_code(400);
    die('Invalid file path');
}

// Delete file
if (file_exists($targetPath)) {
    if (unlink($targetPath)) {
        http_response_code(200);
    } else {
        http_response_code(500);
        die('File deletion failed');
    }
} else {
    http_response_code(404);
    die('File not found');
}
