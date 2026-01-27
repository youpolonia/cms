<?php
/**
 * JTB Layout Gallery API - Get single layout
 * GET /api/jtb/layout-get/{id}
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

header('Content-Type: application/json');

require_once dirname(__DIR__) . '/includes/class-jtb-layout-gallery.php';

$id = (int)($_GET['id'] ?? $_GET['post_id'] ?? 0);

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Layout ID required']);
    exit;
}

try {
    $layout = JTB_Layout_Gallery::get($id);

    if (!$layout) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Layout not found']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'layout' => $layout
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch layout: ' . $e->getMessage()
    ]);
}
