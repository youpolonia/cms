<?php
header('Content-Type: application/json');

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Method not allowed']));
}

require_once __DIR__ . '/../../../core/csrf.php';
csrf_validate_or_403();

if (!isset($_FILES['file'])) {
    http_response_code(400);
    die(json_encode(['error' => 'No file uploaded']));
}

$file = $_FILES['file'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm'];
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'webm'];
$maxSize = 10 * 1024 * 1024; // 10MB

// Verify actual file type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$detectedType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($detectedType, $allowedTypes)) {
    http_response_code(415);
    die(json_encode(['error' => 'Invalid file type']));
}

// Verify extension matches content
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($extension, $allowedExtensions)) {
    http_response_code(415);
    die(json_encode(['error' => 'Invalid file extension']));
}

if ($file['size'] > $maxSize) {
    http_response_code(413);
    die(json_encode(['error' => 'File too large']));
}

$uploadDir = __DIR__ . '/../../../uploads/media/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate secure filename
$filename = bin2hex(random_bytes(16)) . '.' . $extension;
$targetPath = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    // Log successful upload
    file_put_contents(__DIR__ . '/../../../logs/media_uploads.log',
        date('Y-m-d H:i:s') . ' - ' . $_SERVER['REMOTE_ADDR'] . ' - ' . $filename . PHP_EOL,
        FILE_APPEND);
        
    $publicUrl = '/uploads/media/' . $filename;
    echo json_encode(['url' => $publicUrl]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to upload file']);
}
