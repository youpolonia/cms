<?php
/**
 * AI Image Save Proxy
 * 
 * Used by JTB Media Gallery (media-gallery.js) to save AI-generated images to gallery.
 * Uses core/ai_images.php save_to_gallery function.
 *
 * POST /admin/api/ai-image-save.php
 * Body: { url, prompt, csrf_token }
 * Returns: { success: true, ok: true, url: "/uploads/..." }
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(dirname(__DIR__)));
}

require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';

// Start session & verify admin
cms_session_start('admin');
csrf_boot('admin');

if (empty($_SESSION['admin_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Parse JSON body
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['error' => 'Invalid JSON body']);
    exit;
}

// CSRF validation from JSON body
if (!empty($input['csrf_token'])) {
    $_POST['csrf_token'] = $input['csrf_token'];
}
csrf_validate_or_403();

$imageUrl = trim($input['url'] ?? '');
if (empty($imageUrl)) {
    echo json_encode(['error' => 'Image URL is required']);
    exit;
}

// Load AI images module
require_once CMS_ROOT . '/core/database.php';

if (file_exists(CMS_ROOT . '/core/ai_images.php')) {
    require_once CMS_ROOT . '/core/ai_images.php';
}

$prompt = trim($input['prompt'] ?? 'AI Generated Image');

if (function_exists('ai_images_save_to_gallery')) {
    $result = ai_images_save_to_gallery($imageUrl, $prompt, $prompt);
    
    if ($result['ok']) {
        echo json_encode([
            'success' => true,
            'ok' => true,
            'url' => $result['url'] ?? $imageUrl,
            'message' => 'Image saved to gallery'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'ok' => false,
            'error' => $result['error'] ?? 'Save failed'
        ]);
    }
} else {
    // Fallback: image already accessible via URL, just confirm
    echo json_encode([
        'success' => true,
        'ok' => true,
        'url' => $imageUrl,
        'message' => 'Image is accessible'
    ]);
}
