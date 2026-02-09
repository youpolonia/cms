<?php
/**
 * JTB AI API: Generate Content
 * Generates content for specific modules or fields
 *
 * POST /api/jtb/ai/generate-content
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

// Validate required fields
$moduleType = trim($input['module_type'] ?? '');
if (empty($moduleType)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'module_type is required']);
    exit;
}

// Load AI classes
require_once dirname(dirname(__DIR__)) . '/includes/ai/class-jtb-ai-core.php';
require_once dirname(dirname(__DIR__)) . '/includes/ai/class-jtb-ai-context.php';
require_once dirname(dirname(__DIR__)) . '/includes/ai/class-jtb-ai-pexels.php';
require_once dirname(dirname(__DIR__)) . '/includes/ai/class-jtb-ai-styles.php';
require_once dirname(dirname(__DIR__)) . '/includes/ai/class-jtb-ai-content.php';

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

    // Add module-specific context
    if (isset($input['module_index'])) {
        $context[$moduleType . '_index'] = (int)$input['module_index'];
    }

    // Check if regenerating specific field
    $fieldName = $input['field'] ?? null;

    if ($fieldName) {
        // Regenerate single field
        $content = JTB_AI_Content::regenerateField($moduleType, $fieldName, $context);
        $result = [$fieldName => $content];
    } else {
        // Generate full module content
        $result = JTB_AI_Content::generateModuleContent($moduleType, $context);
    }

    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'ok' => true,
        'module_type' => $moduleType,
        'content' => $result,
        'context' => [
            'industry' => $context['industry'],
            'tone' => $context['tone']
        ]
    ]);

} catch (\Exception $e) {
    error_log('JTB AI generate-content error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'An error occurred during content generation'
    ]);
}
