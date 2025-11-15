<?php
require_once __DIR__ . '/../../core/accesschecker.php';
require_once __DIR__ . '/../../core/csrf.php';

// Method guard: POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

// Verify admin permissions
if (!AccessChecker::hasPermission('media.manage')) {
    http_response_code(403);
    die('Access denied');
}

// Initialize CSRF
csrf_boot();

// Verify CSRF token
csrf_validate_or_403();

// Check file upload
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    error_log('Invalid file upload error: ' . ($_FILES['file']['error'] ?? 'none'));
    exit;
}

// Enforce file size limit (5MB)
if ($_FILES['file']['size'] > 5242880) {
    http_response_code(413);
    error_log('File too large: ' . $_FILES['file']['size']);
    exit;
}

// Validate MIME type using finfo
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($_FILES['file']['tmp_name']);
$allowedMimes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp',
    'application/pdf' => 'pdf'
];

if (!isset($allowedMimes[$mimeType])) {
    http_response_code(415);
    error_log('Invalid MIME type: ' . $mimeType);
    exit;
}

// Validate file extension
$pathInfo = pathinfo($_FILES['file']['name']);
$extension = strtolower($pathInfo['extension'] ?? '');
if ($extension !== $allowedMimes[$mimeType]) {
    http_response_code(415);
    error_log('Extension mismatch');
    exit;
}

// Generate random filename
$randomName = bin2hex(random_bytes(16)) . '.' . $extension;

// Secure target directory handling
$baseDir = realpath(__DIR__ . '/../../media/uploads/');
if (!$baseDir) {
    http_response_code(500);
    error_log('Upload directory not found');
    exit;
}
$targetPath = $baseDir . '/' . $randomName;

// Ensure target path is within base directory
if (strncmp($targetPath, $baseDir, strlen($baseDir)) !== 0) {
    http_response_code(400);
    error_log('Path traversal attempt');
    exit;
}

if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
    http_response_code(200);
} else {
    http_response_code(500);
    error_log('File move failed');
    exit;
}
