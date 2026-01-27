<?php
/**
 * JTB Layout Gallery API - Save layout
 * POST /api/jtb/layout-save
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

require_once dirname(__DIR__) . '/includes/class-jtb-layout-gallery.php';

$data = [
    'id' => $_POST['id'] ?? null,
    'name' => $_POST['name'] ?? '',
    'slug' => $_POST['slug'] ?? null,
    'description' => $_POST['description'] ?? null,
    'category' => $_POST['category'] ?? 'general',
    'layout_type' => $_POST['layout_type'] ?? 'page',
    'column_structure' => $_POST['column_structure'] ?? null,
    'content' => $_POST['content'] ?? '{}',
];

if (empty($data['name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Layout name required']);
    exit;
}

// Parse content if string
if (is_string($data['content'])) {
    $data['content'] = json_decode($data['content'], true);
}

try {
    $result = JTB_Layout_Gallery::save($data);

    if ($result) {
        echo json_encode([
            'success' => true,
            'id' => $result,
            'message' => 'Layout saved'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to save layout']);
    }
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to save layout: ' . $e->getMessage()
    ]);
}
