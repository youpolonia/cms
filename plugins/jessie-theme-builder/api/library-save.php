<?php
/**
 * JTB Library API - Save template
 * POST /api/jtb/library-save
 *
 * Body (JSON):
 * - id: (optional) template ID for update
 * - name: template name (required)
 * - description: template description
 * - category_slug: category slug
 * - tags: array of tags
 * - content: template content (required)
 * - thumbnail: thumbnail URL
 * - template_type: page, section, or row
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

header('Content-Type: application/json');

// Ensure tables exist
if (!JTB_Library::tablesExist()) {
    JTB_Library::createTables();
}

// Get JSON body
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    // Try form data
    $data = $_POST;
}

if (empty($data['name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Template name is required']);
    exit;
}

if (!isset($data['content'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Template content is required']);
    exit;
}

try {
    // User-saved templates are never premade
    $data['is_premade'] = 0;
    $data['is_featured'] = 0;

    $result = JTB_Library::save($data);

    if ($result === false) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Failed to save template']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'id' => $result,
        'message' => isset($data['id']) ? 'Template updated' : 'Template saved to library'
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to save template: ' . $e->getMessage()
    ]);
}
