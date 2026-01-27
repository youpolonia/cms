<?php
/**
 * JTB Library API - Delete template
 * POST /api/jtb/library-delete
 *
 * Body (JSON):
 * - id: template ID to delete
 *
 * Note: Premade templates cannot be deleted
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

header('Content-Type: application/json');

// Get JSON body
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    $data = $_POST;
}

$id = $data['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Template ID required']);
    exit;
}

try {
    // Check if template exists
    $template = JTB_Library::get((int)$id);

    if (!$template) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Template not found']);
        exit;
    }

    // Check if premade
    if ($template['is_premade']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Cannot delete premade templates']);
        exit;
    }

    $result = JTB_Library::delete((int)$id);

    if (!$result) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Failed to delete template']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Template deleted'
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to delete template: ' . $e->getMessage()
    ]);
}
