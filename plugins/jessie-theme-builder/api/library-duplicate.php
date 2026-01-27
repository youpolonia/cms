<?php
/**
 * JTB Library API - Duplicate template
 * POST /api/jtb/library-duplicate
 *
 * Body (JSON):
 * - id: template ID to duplicate
 * - name: (optional) new name for the copy
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
$newName = $data['name'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Template ID required']);
    exit;
}

try {
    $result = JTB_Library::duplicate((int)$id, $newName);

    if ($result === false) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Failed to duplicate template']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'id' => $result,
        'message' => 'Template duplicated'
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to duplicate template: ' . $e->getMessage()
    ]);
}
