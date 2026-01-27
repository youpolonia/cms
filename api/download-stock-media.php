<?php
/**
 * Download Stock Media API
 * Downloads stock images/videos from Pexels to Media Library
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
$url = $input['url'] ?? '';
$type = $input['type'] ?? 'image'; // 'image' or 'video'

if (empty($url)) {
    echo json_encode(['success' => false, 'error' => 'URL required']);
    exit;
}

// Validate URL (only allow Pexels domains)
$allowedDomains = ['images.pexels.com', 'videos.pexels.com'];
$parsedUrl = parse_url($url);
$host = $parsedUrl['host'] ?? '';

$isAllowed = false;
foreach ($allowedDomains as $domain) {
    if (strpos($host, $domain) !== false) {
        $isAllowed = true;
        break;
    }
}

if (!$isAllowed) {
    echo json_encode(['success' => false, 'error' => 'Invalid source domain: ' . $host]);
    exit;
}

// Determine file extension and mime type
$extension = '';
$mimeType = '';
if ($type === 'video') {
    $extension = 'mp4';
    $mimeType = 'video/mp4';
} else {
    $path = $parsedUrl['path'] ?? '';
    if (preg_match('/\.(jpg|jpeg)$/i', $path)) {
        $extension = 'jpg';
        $mimeType = 'image/jpeg';
    } elseif (preg_match('/\.png$/i', $path)) {
        $extension = 'png';
        $mimeType = 'image/png';
    } elseif (preg_match('/\.gif$/i', $path)) {
        $extension = 'gif';
        $mimeType = 'image/gif';
    } elseif (preg_match('/\.webp$/i', $path)) {
        $extension = 'webp';
        $mimeType = 'image/webp';
    } else {
        $extension = 'jpg';
        $mimeType = 'image/jpeg';
    }
}

// Generate unique filename
$filename = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
$uploadDir = CMS_ROOT . '/uploads/media/';
$filepath = $uploadDir . $filename;

// Create directory if not exists
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        echo json_encode(['success' => false, 'error' => 'Cannot create upload directory']);
        exit;
    }
}

// Download file
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 120,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; CMS Media Downloader)'
]);

$fileContent = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !$fileContent) {
    echo json_encode(['success' => false, 'error' => 'Failed to download file (HTTP ' . $httpCode . ')']);
    exit;
}

// Save file
if (file_put_contents($filepath, $fileContent) === false) {
    echo json_encode(['success' => false, 'error' => 'Failed to save file']);
    exit;
}

$filesize = filesize($filepath);

// Generate original name from URL
$originalName = 'pexels-' . $type . '-' . substr(md5($url), 0, 8) . '.' . $extension;

// Save to database
try {
    $pdo = \core\Database::connection();
    $stmt = $pdo->prepare("INSERT INTO media (filename, original_name, mime_type, size, path, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $filename,
        $originalName,
        $mimeType,
        $filesize,
        'uploads/media/' . $filename
    ]);
    
    $mediaId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'id' => $mediaId,
        'filename' => $filename,
        'path' => '/uploads/media/' . $filename,
        'size' => $filesize,
        'type' => $type
    ]);
} catch (Exception $e) {
    // File saved but DB failed - still report success but with warning
    echo json_encode([
        'success' => true,
        'filename' => $filename,
        'path' => '/uploads/media/' . $filename,
        'size' => $filesize,
        'type' => $type,
        'warning' => 'File saved but database entry failed'
    ]);
}
