<?php
/**
 * Media Library API
 * Returns list of files from Media Library for Theme Builder
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/session_boot.php';
require_once __DIR__ . '/../core/database.php';

// Check if admin is logged in
cms_session_start('admin');
if (empty($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized', 'files' => []]);
    exit;
}

try {
    $pdo = \core\Database::connection();
    
    // Get all media files, newest first
    $stmt = $pdo->query("SELECT id, filename, original_name, mime_type, size, path, created_at FROM media ORDER BY created_at DESC LIMIT 100");
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $result = [];
    foreach ($files as $file) {
        $result[] = [
            'id' => $file['id'],
            'name' => $file['original_name'] ?: $file['filename'],
            'filename' => $file['filename'],
            'mime_type' => $file['mime_type'],
            'size' => (int)$file['size'],
            'url' => '/uploads/media/' . $file['filename'],
            'created_at' => $file['created_at']
        ];
    }
    
    echo json_encode(['files' => $result]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error', 'files' => []]);
}
