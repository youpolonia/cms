<?php
/**
 * JTB API - Save Template
 * POST /api/jtb/template-save
 *
 * Body: { id?, name, type, content, is_default?, priority?, conditions?: [] }
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

// Validate required fields
if (empty($data['name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Template name is required']);
    exit;
}

if (empty($data['type']) || !in_array($data['type'], JTB_Templates::TYPES)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Valid template type is required (header, footer, body)']);
    exit;
}

try {
    // Prepare template data
    $templateData = [
        'name' => $data['name'],
        'type' => $data['type'],
        'content' => $data['content'] ?? ['version' => '1.0', 'content' => []],
        'is_default' => $data['is_default'] ?? false,
        'priority' => $data['priority'] ?? 10
    ];

    // If updating, include ID
    if (!empty($data['id'])) {
        $templateData['id'] = (int) $data['id'];
    }

    // Save template
    $templateId = JTB_Templates::save($templateData);

    if ($templateId === false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to save template']);
        exit;
    }

    // Save conditions if provided
    if (isset($data['conditions']) && is_array($data['conditions'])) {
        JTB_Template_Conditions::setForTemplate($templateId, $data['conditions']);
    }

    // Get updated template
    $template = JTB_Templates::get($templateId);

    echo json_encode([
        'success' => true,
        'template_id' => $templateId,
        'template' => $template,
        'message' => empty($data['id']) ? 'Template created' : 'Template updated'
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
