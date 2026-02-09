<?php
/**
 * JTB AI API: Generate Section
 * Generates a single section with modules
 *
 * POST /api/jtb/ai/generate-section
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

// Note: Authentication is already handled in router.php
// Note: CSRF is already validated in router.php

header('Content-Type: application/json');

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

// Validate required fields
$sectionType = trim($input['section_type'] ?? '');
if (empty($sectionType)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'section_type is required']);
    exit;
}

// Note: AI classes are already loaded in router.php
// Verify required classes are loaded
if (!class_exists(__NAMESPACE__ . '\\JTB_AI_Generator')) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'AI Generator class not loaded. Check router.php configuration.'
    ]);
    exit;
}

if (!class_exists(__NAMESPACE__ . '\\JTB_AI_Context')) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'AI Context class not loaded. Check router.php configuration.'
    ]);
    exit;
}

try {
    // Build context
    $context = [
        'page_id' => (int)($input['page_id'] ?? 0),
        'industry' => $input['industry'] ?? '',
        'tone' => $input['tone'] ?? 'professional',
        'style' => $input['style'] ?? 'modern',
        'site_name' => $input['site_name'] ?? ''
    ];

    // Merge with page context if page_id provided
    if ($context['page_id'] > 0) {
        $pageContext = JTB_AI_Context::getPageContext($context['page_id']);
        $siteContext = JTB_AI_Context::getSiteContext();

        $context['page_title'] = $pageContext['title'] ?? '';
        $context['site_name'] = $context['site_name'] ?: ($siteContext['name'] ?? '');
        $context['industry'] = $context['industry'] ?: ($siteContext['industry'] ?? '');
    }

    // Get branding context
    $branding = JTB_AI_Context::getBrandingContext();
    $context['colors'] = [
        'primary' => $branding['primary_color'],
        'secondary' => $branding['secondary_color'],
        'accent' => $branding['accent_color']
    ];

    // Add section-specific options
    if (isset($input['num_items'])) {
        $context['num_items'] = (int)$input['num_items'];
    }

    // Generate section
    $section = JTB_AI_Generator::generateSection($sectionType, $context);

    if (empty($section)) {
        http_response_code(422);
        echo json_encode([
            'ok' => false,
            'error' => 'Failed to generate section'
        ]);
        exit;
    }

    // Apply branding if requested
    if (!empty($input['apply_branding']) && !empty($context['colors'])) {
        $section = JTB_AI_Generator::applyBranding([$section], $context['colors'])[0];
    }

    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'ok' => true,
        'section' => $section,
        'section_type' => $sectionType,
        'context' => [
            'industry' => $context['industry'],
            'tone' => $context['tone']
        ]
    ]);

} catch (\Exception $e) {
    error_log('JTB AI generate-section error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'An error occurred during section generation'
    ]);
}
