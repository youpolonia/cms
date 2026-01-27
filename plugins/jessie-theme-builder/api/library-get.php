<?php
/**
 * JTB Library API - Get single template
 * GET /api/jtb/library-get/{id}
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

header('Content-Type: application/json');

$id = $_GET['post_id'] ?? $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Template ID required']);
    exit;
}

try {
    $template = JTB_Library::get((int)$id);

    if (!$template) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Template not found']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'template' => $template
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch template: ' . $e->getMessage()
    ]);
}
