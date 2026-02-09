<?php
/**
 * JTB AI API: Generate Image
 * Generates AI images from text prompts
 *
 * POST /api/jtb/ai/generate-image
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

// Require authentication (check both admin_id and user_id like router.php)
if (empty($_SESSION['admin_id']) && empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Authentication required']);
    exit;
}

// Require POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// Note: CSRF is already validated in router.php

// Parse input
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON input']);
    exit;
}

// Load AI classes
require_once dirname(dirname(__DIR__)) . '/includes/ai/class-jtb-ai-images.php';
require_once dirname(dirname(__DIR__)) . '/includes/ai/class-jtb-ai-context.php';

try {
    // Determine generation type
    $type = $input['type'] ?? 'custom';
    $prompt = trim($input['prompt'] ?? '');

    // Build options
    $options = [
        'width' => (int)($input['width'] ?? 1024),
        'height' => (int)($input['height'] ?? 1024),
        'style' => $input['style'] ?? 'photorealistic'
    ];

    // Build context for type-specific generation
    $context = [
        'industry' => $input['industry'] ?? '',
        'style' => $input['style'] ?? 'professional'
    ];

    // Merge with page context if available
    if (!empty($input['page_id'])) {
        $pageContext = JTB_AI_Context::getPageContext((int)$input['page_id']);
        $siteContext = JTB_AI_Context::getSiteContext();

        $context['site_name'] = $siteContext['name'] ?? '';
        $context['industry'] = $context['industry'] ?: ($siteContext['industry'] ?? '');
    }

    // Generate based on type
    $result = match($type) {
        'hero' => JTB_AI_Images::generateHeroImage($context),
        'background' => JTB_AI_Images::generateBackgroundImage(array_merge($context, [
            'background_type' => $input['background_type'] ?? 'abstract'
        ])),
        'feature' => JTB_AI_Images::generateFeatureIcon(array_merge($context, [
            'feature' => $input['feature'] ?? 'feature',
            'icon_style' => $input['icon_style'] ?? 'flat'
        ])),
        'team' => JTB_AI_Images::generateTeamPhoto(array_merge($context, [
            'gender' => $input['gender'] ?? 'neutral',
            'role' => $input['role'] ?? 'professional'
        ])),
        'product' => JTB_AI_Images::generateProductImage(array_merge($context, [
            'product' => $input['product'] ?? 'product',
            'product_style' => $input['product_style'] ?? 'professional'
        ])),
        'testimonial' => JTB_AI_Images::generateTestimonialAvatar(array_merge($context, [
            'gender' => $input['gender'] ?? 'neutral',
            'age' => $input['age'] ?? 'middle-aged'
        ])),
        'custom' => empty($prompt)
            ? ['ok' => false, 'error' => 'Prompt is required for custom image generation']
            : JTB_AI_Images::generateImage($prompt, $options),
        default => ['ok' => false, 'error' => 'Unknown image type: ' . $type]
    };

    if (!$result['ok']) {
        http_response_code(422);
        echo json_encode([
            'ok' => false,
            'error' => $result['error'] ?? 'Image generation failed'
        ]);
        exit;
    }

    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'ok' => true,
        'url' => $result['local_url'] ?? $result['url'],
        'media_id' => $result['media_id'] ?? null,
        'source' => $result['source'] ?? null,
        'width' => $options['width'],
        'height' => $options['height']
    ]);

} catch (\Exception $e) {
    error_log('JTB AI generate-image error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'An error occurred during image generation'
    ]);
}
