<?php
require_once __DIR__ . '/../core/accesschecker.php';

// Check admin permissions
if (!AccessChecker::hasPermission('media.manage')) {
    header('HTTP/1.1 403 Forbidden');
    die('Access denied');
}

// Get request data
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['path'])) {
    header('HTTP/1.1 400 Bad Request');
    die('Invalid request');
}

// Validate and sanitize path
$path = realpath(__DIR__ . '/../' . ltrim($data['path'], '/'));
if (!$path || strpos($path, realpath(__DIR__ . '/../media/')) !== 0) {
    header('HTTP/1.1 400 Bad Request');
    die('Invalid file path');
}

// Delete file
if (file_exists($path)) {
    if (unlink($path)) {
        header('HTTP/1.1 200 OK');
        echo json_encode(['success' => true]);
    } else {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['error' => 'Failed to delete file']);
    }
} else {
    header('HTTP/1.1 404 Not Found');
    echo json_encode(['error' => 'File not found']);
}
