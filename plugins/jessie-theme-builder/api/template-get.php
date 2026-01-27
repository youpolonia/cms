<?php
/**
 * JTB API - Get Single Template
 * GET /api/jtb/template-get/{id}
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

header('Content-Type: application/json');

// Authentication is already checked in router.php

// Only GET allowed
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get template ID
$templateId = $_GET['post_id'] ?? $_GET['id'] ?? null;

if (!$templateId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Template ID required']);
    exit;
}

try {
    $template = JTB_Templates::get((int) $templateId);

    if (!$template) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Template not found']);
        exit;
    }

    // Get page types for conditions UI
    $pageTypes = JTB_Template_Conditions::getPageTypes();

    echo json_encode([
        'success' => true,
        'template' => $template,
        'pageTypes' => $pageTypes
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
