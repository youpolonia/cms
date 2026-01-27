<?php
/**
 * JTB Layout Gallery API - Delete layout
 * POST /api/jtb/layout-delete
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

$id = (int)($_POST['id'] ?? 0);

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Layout ID required']);
    exit;
}

try {
    $success = JTB_Layout_Gallery::delete($id);

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Layout deleted'
        ]);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Cannot delete premade layout']);
    }
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to delete layout: ' . $e->getMessage()
    ]);
}
