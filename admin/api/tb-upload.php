<?php
/**
 * Theme Builder Media Upload API
 * Simple upload endpoint for Theme Builder
 */

define('CMS_ROOT', dirname(__DIR__, 2));

require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');

header('Content-Type: application/json; charset=UTF-8');

// Check admin session (simpler check)
if (empty($_SESSION['admin_id']) && empty($_SESSION['admin_authenticated'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// Validate CSRF
$csrfSent = $_POST['csrf_token'] ?? '';
$csrfSession = $_SESSION['csrf_token'] ?? '';
if (empty($csrfSent) || empty($csrfSession) || !hash_equals($csrfSession, $csrfSent)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

// Check file
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'File too large (server limit)',
        UPLOAD_ERR_FORM_SIZE => 'File too large',
        UPLOAD_ERR_PARTIAL => 'Partial upload',
        UPLOAD_ERR_NO_FILE => 'No file uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Server error',
        UPLOAD_ERR_CANT_WRITE => 'Cannot write file',
        UPLOAD_ERR_EXTENSION => 'Extension blocked'
    ];
    $code = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
    echo json_encode(['ok' => false, 'error' => $errors[$code] ?? 'Upload error']);
    exit;
}

$file = $_FILES['file'];

// Check size (10MB)
if ($file['size'] > 10 * 1024 * 1024) {
    echo json_encode(['ok' => false, 'error' => 'File too large (max 10MB)']);
    exit;
}

// Validate MIME
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowed = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp',
    'image/svg+xml' => 'svg'
];

if (!isset($allowed[$mime])) {
    echo json_encode(['ok' => false, 'error' => 'Invalid file type: ' . $mime]);
    exit;
}

// Ensure directory
$uploadDir = CMS_ROOT . '/uploads/media/';
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
    echo json_encode(['ok' => false, 'error' => 'Cannot create directory']);
    exit;
}

// Generate filename
$ext = $allowed[$mime];
$filename = date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
$target = $uploadDir . $filename;

// Move file
if (!move_uploaded_file($file['tmp_name'], $target)) {
    echo json_encode(['ok' => false, 'error' => 'Failed to save file']);
    exit;
}

// Success
echo json_encode([
    'ok' => true,
    'success' => true,
    'url' => '/uploads/media/' . $filename,
    'filename' => $filename,
    'file' => [
        'url' => '/uploads/media/' . $filename,
        'name' => $filename
    ]
]);
