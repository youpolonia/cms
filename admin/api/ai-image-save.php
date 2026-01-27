<?php
/**
 * AI Image Save API Endpoint
 * Saves a generated AI image to the media gallery
 * Called when user clicks "Use This Image"
 */

define('CMS_ROOT', realpath(__DIR__ . '/../..'));

require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';

cms_session_start('admin');

header('Content-Type: application/json');

// Check auth
if (empty($_SESSION['admin_id']) && empty($_SESSION['admin_authenticated'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// CSRF validation
$csrfToken = $input['csrf_token'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';
if (empty($csrfToken) || empty($sessionToken) || !hash_equals($sessionToken, $csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

// Validate inputs
$tempFile = $input['tempFile'] ?? '';
$tempUrl = $input['url'] ?? '';
$prompt = $input['prompt'] ?? 'AI Generated Image';

if (empty($tempUrl)) {
    echo json_encode(['success' => false, 'error' => 'No image URL provided']);
    exit;
}

// Load AI Images module for save function
if (file_exists(CMS_ROOT . '/core/ai_images.php')) {
    require_once CMS_ROOT . '/core/ai_images.php';
}

// Try to save to gallery if function exists
if (function_exists('ai_images_save_to_gallery') && !empty($tempFile)) {
    $galleryResult = ai_images_save_to_gallery($tempFile, $prompt, $prompt);
    if ($galleryResult['ok'] && !empty($galleryResult['url'])) {
        echo json_encode([
            'success' => true,
            'url' => $galleryResult['url'],
            'message' => 'Image saved to gallery'
        ]);
        exit;
    }
}

// Fallback: if already in media, just return the URL
// The temp file from ai_images_generate is often already saved to temp location
// Just copy it to permanent media folder

$mediaDir = CMS_ROOT . '/uploads/media/';
if (!is_dir($mediaDir)) {
    @mkdir($mediaDir, 0755, true);
}

// If URL is already a local path, it might be temp - copy to permanent
if (strpos($tempUrl, '/uploads/') === 0) {
    $sourcePath = CMS_ROOT . $tempUrl;
    if (file_exists($sourcePath)) {
        $filename = 'ai_' . time() . '_' . uniqid() . '.png';
        $destPath = $mediaDir . $filename;

        if (copy($sourcePath, $destPath)) {
            echo json_encode([
                'success' => true,
                'url' => '/uploads/media/' . $filename,
                'message' => 'Image saved to gallery'
            ]);
            exit;
        }
    }
}

// If nothing else works, just return the original URL
echo json_encode([
    'success' => true,
    'url' => $tempUrl,
    'message' => 'Using existing image'
]);
