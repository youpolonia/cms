<?php
/**
 * JTB API - Preview Template
 * POST /api/jtb/template-preview
 *
 * Body: { content }
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

// Authentication and CSRF are checked in router.php

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get request body
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

// Validate content
if (!isset($data['content'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Content is required']);
    exit;
}

try {
    $content = $data['content'];

    // Generate HTML and CSS
    $html = JTB_Renderer::render($content);
    $css = JTB_Renderer::generateCss($content);

    echo json_encode([
        'success' => true,
        'html' => $html,
        'css' => $css
    ]);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}
