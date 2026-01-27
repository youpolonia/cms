<?php
/**
 * AI Image Generator API Endpoint
 * Uses core/ai_images.php - same as admin/ai-images.php
 *
 * MODIFIED: Does NOT auto-save to gallery. Returns temp URL.
 * User must explicitly save via ai-image-save.php endpoint.
 */

define('CMS_ROOT', realpath(__DIR__ . '/../..'));

require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';

cms_session_start('admin');

header('Content-Type: application/json');

// Check auth (supports both MVC and legacy session vars)
if (empty($_SESSION['admin_id']) && empty($_SESSION['admin_authenticated'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// CSRF validation
$csrfToken = $input['csrf_token'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';
if (empty($csrfToken) || empty($sessionToken) || !hash_equals($sessionToken, $csrfToken)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

// Validate prompt
$prompt = trim($input['prompt'] ?? '');
if (empty($prompt)) {
    echo json_encode(['ok' => false, 'error' => 'Please provide an image description']);
    exit;
}

// Load AI Images module (same as admin/ai-images.php)
if (!file_exists(CMS_ROOT . '/core/ai_images.php')) {
    echo json_encode(['ok' => false, 'error' => 'AI Images module not found']);
    exit;
}

require_once CMS_ROOT . '/core/ai_images.php';

if (!function_exists('ai_images_is_configured') || !ai_images_is_configured()) {
    echo json_encode(['ok' => false, 'error' => 'AI not configured. Please add OpenAI API key in Settings.']);
    exit;
}

// Map style from article editor to ai_images format
$style = $input['style'] ?? 'photorealistic';
$size = $input['size'] ?? '1024x1024';

// Convert size to aspect ratio
$aspectMap = [
    '1024x1024' => '1:1',
    '1792x1024' => '16:9',
    '1024x1792' => '9:16'
];
$aspect = $aspectMap[$size] ?? '1:1';

// Prepare form data for ai_images_generate
$form = [
    'prompt' => $prompt,
    'style' => $style,
    'aspect' => $aspect,
    'quality' => 'standard',
    'notes' => '',
    'seo_name' => ''
];

// Generate image using core module
$result = ai_images_generate($form);

if ($result['ok']) {
    // Return temp path - DO NOT save to gallery yet
    // User will explicitly save when clicking "Use This Image"
    echo json_encode([
        'success' => true,
        'url' => $result['path'],
        'tempFile' => $result['file'] ?? '',
        'prompt' => $prompt,
        'model' => $result['model'] ?? 'dall-e-3'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => $result['error'] ?? 'Generation failed'
    ]);
}
