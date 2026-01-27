<?php
/**
 * AI Image Generator API
 * Generates images using OpenAI DALL-E
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/session_boot.php';
require_once __DIR__ . '/../core/ai_images.php';

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
$prompt = trim($input['prompt'] ?? '');
$size = $input['size'] ?? '1024x1024';
$style = $input['style'] ?? 'vivid';
$quality = $input['quality'] ?? 'standard';

if (empty($prompt)) {
    echo json_encode(['success' => false, 'error' => 'Prompt is required']);
    exit;
}

// Map size to aspect ratio for ai_images_generate
$aspectMap = [
    '1024x1024' => '1:1',
    '1792x1024' => '16:9',
    '1024x1792' => '9:16'
];
$aspect = $aspectMap[$size] ?? '1:1';

// Map style
$styleMap = [
    'vivid' => '',
    'natural' => 'photorealistic'
];
$mappedStyle = $styleMap[$style] ?? '';

// Check if function exists
if (!function_exists('ai_images_generate')) {
    echo json_encode(['success' => false, 'error' => 'AI Image generator not available']);
    exit;
}

// Generate image
$result = ai_images_generate([
    'prompt' => $prompt,
    'aspect' => $aspect,
    'style' => $mappedStyle,
    'quality' => $quality,
    'seo_name' => ''
]);

if (!empty($result['ok']) && !empty($result['path'])) {
    echo json_encode([
        'success' => true,
        'url' => $result['path'],
        'revised_prompt' => $result['revised_prompt'] ?? ''
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => $result['error'] ?? 'Generation failed'
    ]);
}
