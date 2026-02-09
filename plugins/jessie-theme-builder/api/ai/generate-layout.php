<?php
/**
 * JTB AI API: Generate Layout
 * Generates complete page layouts from text prompts
 *
 * POST /api/jtb/ai/generate-layout
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

// Always set content type for JSON
header('Content-Type: application/json');

// Note: Authentication is already handled in router.php
// Note: CSRF is already validated in router.php
// Note: AI classes are already loaded in router.php

// Require POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// Parse input
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON input']);
    exit;
}

// Validate required fields
$prompt = trim($input['prompt'] ?? '');
if (empty($prompt)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Prompt is required']);
    exit;
}

try {
    // Build options
    $options = [
        'page_id' => (int)($input['page_id'] ?? 0),
        'generate_images' => !empty($input['generate_images']),
        'apply_branding' => $input['apply_branding'] ?? true,
        'industry' => $input['industry'] ?? null,
        'tone' => $input['tone'] ?? null,
        'style' => $input['style'] ?? null,
        'page_type' => $input['page_type'] ?? null,
        'sections' => $input['sections'] ?? null
    ];

    // Check if generator class exists
    if (!class_exists('JessieThemeBuilder\\JTB_AI_Generator')) {
        echo json_encode(['ok' => false, 'error' => 'JTB_AI_Generator class not found']);
        exit;
    }

    // Generate layout with quality validation
    $result = JTB_AI_Generator::generateWithValidation([
        'prompt' => $prompt,
        'options' => $options,
    ]);

    // Extract quality data before sending response
    $quality = $result['_quality'] ?? null;
    unset($result['_quality']);

    if (!$result['ok']) {
        http_response_code(422);
        echo json_encode([
            'ok' => false,
            'error' => $result['error'] ?? 'Layout generation failed',
            'quality' => $quality,
        ]);
        exit;
    }

    // Generate images if requested
    if (!empty($options['generate_images']) && !empty($result['sections'])) {
        require_once dirname(dirname(__DIR__)) . '/includes/ai/class-jtb-ai-images.php';
        $imageResults = JTB_AI_Images::generateImagesForLayout(['sections' => $result['sections']]);
        $result['image_results'] = $imageResults;
    }

    // Return success response with quality data
    header('Content-Type: application/json');
    echo json_encode([
        'ok' => true,
        'layout' => [
            'version' => '1.0',
            'content' => $result['sections']
        ],
        'intent' => $result['intent'] ?? null,
        'image_results' => $result['image_results'] ?? null,
        'quality' => $quality,
    ]);

} catch (\Exception $e) {
    error_log('JTB AI generate-layout error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'An error occurred during layout generation'
    ]);
}
