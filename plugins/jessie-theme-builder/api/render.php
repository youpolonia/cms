<?php
/**
 * Render API Endpoint
 * POST /api/jtb/render
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

header('Content-Type: application/json');

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Authentication and CSRF are checked in router.php

// Get content
$content = isset($_POST['content']) ? $_POST['content'] : '';

if (empty($content)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Content is required']);
    exit;
}

// Validate JSON
$contentArray = json_decode($content, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON content: ' . json_last_error_msg()]);
    exit;
}

// Validate structure
if (!JTB_Builder::validateContent($contentArray)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid content structure']);
    exit;
}

// Render content
try {
    $html = JTB_Renderer::render($contentArray);
    $css = JTB_Renderer::getCss();

    echo json_encode([
        'success' => true,
        'data' => [
            'html' => $html,
            'css' => $css
        ]
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Render error: ' . $e->getMessage()]);
}
