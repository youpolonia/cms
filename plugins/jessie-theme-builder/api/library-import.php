<?php
/**
 * JTB Library API - Import template from JSON
 * POST /api/jtb/library-import
 *
 * Accepts either:
 * - JSON body with template data
 * - File upload (field name: file)
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

header('Content-Type: application/json');

// Ensure tables exist
if (!JTB_Library::tablesExist()) {
    JTB_Library::createTables();
}

$data = null;

// Check for file upload
if (!empty($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $contents = file_get_contents($_FILES['file']['tmp_name']);
    $data = json_decode($contents, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid JSON file']);
        exit;
    }
} else {
    // Try JSON body
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
}

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No template data provided']);
    exit;
}

// Validate structure
if (!isset($data['jtb_template']) || !isset($data['content'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid template format. Expected jtb_template and content fields.']);
    exit;
}

try {
    $result = JTB_Library::import($data);

    if ($result === false) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Failed to import template']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'id' => $result,
        'message' => 'Template imported successfully',
        'name' => $data['jtb_template']['name'] ?? 'Imported Template'
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to import template: ' . $e->getMessage()
    ]);
}
