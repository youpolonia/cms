<?php
/**
 * JTB API - Delete Template
 * POST /api/jtb/template-delete
 *
 * Body: { id }
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

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

// Validate ID
if (empty($data['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Template ID is required']);
    exit;
}

try {
    $templateId = (int) $data['id'];

    // Check if template exists
    $template = JTB_Templates::get($templateId);
    if (!$template) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Template not found']);
        exit;
    }

    // Delete template (conditions are deleted via CASCADE)
    $success = JTB_Templates::delete($templateId);

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Template deleted'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to delete template']);
    }

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
