<?php
/**
 * Media Delete API Endpoint
 * POST /api/jtb/media-delete
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

header('Content-Type: application/json');

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Authentication and CSRF are checked in router.php

// Get URL from POST data
$url = $_POST['url'] ?? '';

if (empty($url)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'URL is required']);
    exit;
}

// Validate URL starts with our upload path
if (strpos($url, '/uploads/jtb/') !== 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid media URL']);
    exit;
}

// Build full file path
$filePath = CMS_ROOT . $url;

// Sanitize path to prevent directory traversal
$realPath = realpath($filePath);
$uploadDir = realpath(CMS_ROOT . '/uploads/jtb');

if ($realPath === false || strpos($realPath, $uploadDir) !== 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid file path']);
    exit;
}

// Check if file exists
if (!file_exists($realPath)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'File not found']);
    exit;
}

// Delete the file
if (!unlink($realPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to delete file']);
    exit;
}

// Return success
echo json_encode([
    'success' => true,
    'data' => ['url' => $url]
]);
