<?php
/**
 * Theme Builder - Download external image to server
 * POST /admin/api/tb-download-image.php
 * 
 * Accepts: { "url": "https://..." }
 * Returns: { "success": true, "local_url": "/uploads/media/..." }
 */

define('CMS_ROOT', dirname(__DIR__, 2));

require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

header('Content-Type: application/json; charset=UTF-8');

// Check admin session
if (empty($_SESSION['admin_id']) && empty($_SESSION['admin_authenticated'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);
$url = $input['url'] ?? '';

if (empty($url)) {
    echo json_encode(['success' => false, 'error' => 'No URL provided']);
    exit;
}

// Validate URL
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid URL']);
    exit;
}

// Only allow http/https
if (!preg_match('/^https?:\/\//i', $url)) {
    echo json_encode(['success' => false, 'error' => 'Only HTTP/HTTPS URLs allowed']);
    exit;
}

// Check if already local
if (str_starts_with($url, '/uploads/') || str_starts_with($url, '/assets/')) {
    echo json_encode(['success' => true, 'local_url' => $url, 'message' => 'Already local']);
    exit;
}

try {
    // Create upload directory
    $uploadDir = CMS_ROOT . '/public/uploads/media/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate filename
    $ext = 'jpg';
    $parsedPath = parse_url($url, PHP_URL_PATH);
    if ($parsedPath && preg_match('/\.(jpe?g|png|gif|webp)$/i', $parsedPath, $m)) {
        $ext = strtolower($m[1] === 'jpeg' ? 'jpg' : $m[1]);
    }
    
    $filename = 'tb_' . date('Ymd_His') . '_' . substr(md5($url . microtime(true)), 0, 8) . '.' . $ext;
    $filepath = $uploadDir . $filename;
    
    // Download image
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_HTTPHEADER => ['Accept: image/*']
    ]);
    
    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        echo json_encode(['success' => false, 'error' => "HTTP error: $httpCode" . ($error ? " - $error" : "")]);
        exit;
    }
    
    if (empty($imageData) || strlen($imageData) < 100) {
        echo json_encode(['success' => false, 'error' => 'Empty or invalid response']);
        exit;
    }
    
    // Verify it's an image using magic bytes
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->buffer($imageData);
    
    if (!str_starts_with($mimeType, 'image/')) {
        echo json_encode(['success' => false, 'error' => "Not an image: $mimeType"]);
        exit;
    }
    
    // Adjust extension based on actual mime type
    $mimeToExt = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp'
    ];
    if (isset($mimeToExt[$mimeType])) {
        $newExt = $mimeToExt[$mimeType];
        if ($newExt !== $ext) {
            $filename = preg_replace('/\.[^.]+$/', '.' . $newExt, $filename);
            $filepath = $uploadDir . $filename;
        }
    }
    
    // Save file
    if (file_put_contents($filepath, $imageData) === false) {
        echo json_encode(['success' => false, 'error' => 'Failed to save file']);
        exit;
    }
    
    chmod($filepath, 0644);
    
    $localUrl = '/uploads/media/' . $filename;
    $fileSize = strlen($imageData);
    
    // Add to media library database
    $pdo = db();
    $stmt = $pdo->prepare("
        INSERT INTO media (filename, original_name, mime_type, size, path, title, alt_text, folder, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'media', NOW())
    ");
    
    // Extract title from URL or use filename
    $urlPath = parse_url($url, PHP_URL_PATH);
    $originalName = $urlPath ? basename($urlPath) : $filename;
    $title = pathinfo($originalName, PATHINFO_FILENAME);
    
    $stmt->execute([
        $filename,
        $originalName,
        $mimeType,
        $fileSize,
        $localUrl,
        $title,
        $title // alt_text same as title
    ]);
    
    $mediaId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'local_url' => $localUrl,
        'filename' => $filename,
        'size' => $fileSize,
        'mime' => $mimeType,
        'media_id' => $mediaId
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
