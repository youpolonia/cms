<?php
/**
 * Upload API Endpoint
 * POST /api/jtb/upload
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

// Check if file was uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $errorMessage = 'File upload failed';

    if (isset($_FILES['file']['error'])) {
        switch ($_FILES['file']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errorMessage = 'File is too large';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errorMessage = 'File was only partially uploaded';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMessage = 'No file was uploaded';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
            case UPLOAD_ERR_CANT_WRITE:
                $errorMessage = 'Server configuration error';
                break;
        }
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $errorMessage]);
    exit;
}

$file = $_FILES['file'];

// Allowed MIME types
$allowedTypes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp',
    'image/svg+xml' => 'svg'
];

// Validate MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!isset($allowedTypes[$mimeType])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid file type. Allowed: JPEG, PNG, GIF, WebP, SVG']);
    exit;
}

// Validate file size (10MB max)
$maxSize = 10 * 1024 * 1024; // 10MB

if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'File is too large. Maximum size: 10MB']);
    exit;
}

// Generate unique filename
$extension = $allowedTypes[$mimeType];
$filename = 'jtb_' . uniqid() . '_' . time() . '.' . $extension;

// Create upload directory
$year = date('Y');
$month = date('m');
$uploadDir = CMS_ROOT . '/uploads/jtb/' . $year . '/' . $month;

if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to create upload directory']);
        exit;
    }
}

// Move uploaded file
$targetPath = $uploadDir . '/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save uploaded file']);
    exit;
}

// Get image dimensions (for images only)
$width = 0;
$height = 0;

if ($mimeType !== 'image/svg+xml') {
    $imageInfo = getimagesize($targetPath);
    if ($imageInfo) {
        $width = $imageInfo[0];
        $height = $imageInfo[1];
    }
}

// Build URL
$url = '/uploads/jtb/' . $year . '/' . $month . '/' . $filename;

// Return response
echo json_encode([
    'success' => true,
    'data' => [
        'url' => $url,
        'filename' => $filename,
        'size' => $file['size'],
        'type' => $mimeType,
        'width' => $width,
        'height' => $height
    ]
]);
