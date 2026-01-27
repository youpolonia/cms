<?php
/**
 * Simple image upload endpoint for Article Editor
 * Returns JSON: {success: true, url: '/uploads/media/...', filename: '...'}
 * 
 * Pure PHP, no frameworks, no Composer, no CLI
 * DO NOT add closing ?> tag
 */

declare(strict_types=1);

define('CMS_ROOT', dirname(__DIR__, 2));

require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');

require_once CMS_ROOT . '/admin/includes/permissions.php';
cms_require_admin_role();

header('Content-Type: application/json; charset=UTF-8');

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Validate CSRF
if (!csrf_validate($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

// Check file
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload limit',
        UPLOAD_ERR_FORM_SIZE => 'File too large',
        UPLOAD_ERR_PARTIAL => 'Partial upload',
        UPLOAD_ERR_NO_FILE => 'No file uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Server config error',
        UPLOAD_ERR_CANT_WRITE => 'Cannot write file',
        UPLOAD_ERR_EXTENSION => 'Blocked by extension'
    ];
    $code = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
    echo json_encode(['success' => false, 'error' => $errorMessages[$code] ?? 'Upload error']);
    exit;
}

$file = $_FILES['file'];

// Check size (10MB max)
$maxSize = 10 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'error' => 'File too large (max 10MB)']);
    exit;
}

// Validate MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowedTypes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp',
    'image/svg+xml' => 'svg'
];

if (!isset($allowedTypes[$mimeType])) {
    echo json_encode(['success' => false, 'error' => 'Invalid image type: ' . $mimeType]);
    exit;
}

// Ensure upload directory
$uploadDir = CMS_ROOT . '/uploads/media/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        echo json_encode(['success' => false, 'error' => 'Cannot create upload directory']);
        exit;
    }
}

// Generate unique filename
$extension = $allowedTypes[$mimeType];
$filename = date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
$targetPath = $uploadDir . $filename;

// Move file
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode(['success' => false, 'error' => 'Failed to save file']);
    exit;
}

// Set proper permissions
chmod($targetPath, 0644);

// Success
echo json_encode([
    'success' => true,
    'url' => '/uploads/media/' . $filename,
    'filename' => $filename,
    'size' => $file['size'],
    'mime' => $mimeType
]);