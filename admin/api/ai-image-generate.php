<?php
/**
 * AI Image Generation Proxy
 * 
 * Used by JTB Media Gallery (media-gallery.js) for AI Generate tab.
 * Uses core/ai_images.php DALL-E integration.
 *
 * POST /admin/api/ai-image-generate.php
 * Body: { prompt, style, size, csrf_token }
 * Returns: { success: true, ok: true, url: "/uploads/ai-images/..." }
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

$prompt = trim($input['prompt'] ?? '');
if (empty($prompt)) {
    echo json_encode(['error' => 'Prompt is required']);
    exit;
}

// Load AI images module
if (!file_exists(CMS_ROOT . '/core/ai_images.php')) {
    echo json_encode(['error' => 'AI Images module not available']);
    exit;
}

require_once CMS_ROOT . '/core/database.php';
require_once CMS_ROOT . '/core/ai_images.php';

if (!ai_images_is_configured()) {
    echo json_encode(['error' => 'AI Images not configured. Please add OpenAI API key in AI Settings.']);
    exit;
}

// Map size string to aspect ratio
$size = $input['size'] ?? '1024x1024';
$aspectMap = [
    '1024x1024' => '1:1',
    '1792x1024' => '16:9',
    '1024x1792' => '9:16',
];
$aspect = $aspectMap[$size] ?? '1:1';

$style = $input['style'] ?? 'photorealistic';

// Build spec for ai_images_generate()
$spec = [
    'prompt' => $prompt,
    'style' => $style,
    'aspect' => $aspect,
    'quality' => 'standard',
    'notes' => '',
    'seo_name' => ''
];

$result = ai_images_generate($spec);

if ($result['ok']) {
    echo json_encode([
        'success' => true,
        'ok' => true,
        'url' => $result['path'],
        'path' => $result['path'],
        'prompt' => $prompt
    ]);
} else {
    echo json_encode([
        'success' => false,
        'ok' => false,
        'error' => $result['error'] ?? 'Generation failed'
    ]);
}
