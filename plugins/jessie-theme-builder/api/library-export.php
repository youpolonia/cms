<?php
/**
 * JTB Library API - Export template as JSON
 * GET /api/jtb/library-export/{id}
 *
 * Returns downloadable JSON file
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

$id = $_GET['post_id'] ?? $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Template ID required']);
    exit;
}

try {
    $exportData = JTB_Library::export((int)$id);

    if (!$exportData) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Template not found']);
        exit;
    }

    // Generate filename
    $filename = 'jtb-template-' . preg_replace('/[^a-z0-9-]/', '', strtolower($exportData['jtb_template']['name'])) . '.json';

    // Send as downloadable file
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');

    echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (\Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Failed to export template: ' . $e->getMessage()
    ]);
}
