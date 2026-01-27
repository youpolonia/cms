<?php
/**
 * Save AI Image to Media Library
 * Copies AI-generated image from /uploads/ai-images/ to /uploads/media/ and adds to DB
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/session_boot.php';
require_once __DIR__ . '/../core/database.php';

// Check if admin is logged in
cms_session_start('admin');
if (empty($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'POST method required']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$url = trim($input['url'] ?? '');

if (empty($url)) {
    echo json_encode(['success' => false, 'error' => 'URL is required']);
    exit;
}

// Extract filename from URL
$filename = basename(parse_url($url, PHP_URL_PATH));
if (empty($filename)) {
    echo json_encode(['success' => false, 'error' => 'Invalid URL']);
    exit;
}

// Check if it's from AI images folder
$sourcePath = CMS_ROOT . '/uploads/ai-images/' . $filename;
if (!file_exists($sourcePath)) {
    echo json_encode(['success' => false, 'error' => 'Source file not found']);
    exit;
}

// Copy to media folder
$mediaDir = CMS_ROOT . '/uploads/media/';
if (!is_dir($mediaDir)) {
    mkdir($mediaDir, 0755, true);
}

$newFilename = 'ai-' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . pathinfo($filename, PATHINFO_EXTENSION);
$destPath = $mediaDir . $newFilename;

if (!copy($sourcePath, $destPath)) {
    echo json_encode(['success' => false, 'error' => 'Failed to copy file']);
    exit;
}

// Get file info
$filesize = filesize($destPath);
$mimeType = mime_content_type($destPath) ?: 'image/png';

// Add to database
try {
    $pdo = \core\Database::connection();
    $stmt = $pdo->prepare("INSERT INTO media (filename, original_name, mime_type, size, path, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $newFilename,
        'AI Generated - ' . substr($filename, 0, 30),
        $mimeType,
        $filesize,
        'uploads/media/' . $newFilename
    ]);
    
    echo json_encode([
        'success' => true,
        'id' => $pdo->lastInsertId(),
        'url' => '/uploads/media/' . $newFilename,
        'filename' => $newFilename
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
