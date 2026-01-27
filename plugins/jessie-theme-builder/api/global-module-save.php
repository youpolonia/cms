<?php
/**
 * JTB API - Save Global Module
 * POST /api/jtb/global-module-save
 *
 * Body: { id?, name, type, content, description? }
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
    echo json_encode(['success' => false, 'error' => 'Module name is required']);
    exit;
}

if (empty($data['type'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Module type is required']);
    exit;
}

if (!isset($data['content'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Module content is required']);
    exit;
}

try {
    // Check for duplicate name (only for new modules)
    if (empty($data['id']) && JTB_Global_Modules::nameExists($data['name'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'A module with this name already exists']);
        exit;
    }

    // Prepare module data
    $moduleData = [
        'name' => $data['name'],
        'type' => $data['type'],
        'content' => $data['content'],
        'description' => $data['description'] ?? null,
        'thumbnail' => $data['thumbnail'] ?? null
    ];

    // If updating, include ID
    if (!empty($data['id'])) {
        $moduleData['id'] = (int) $data['id'];
    }

    // Save module
    $moduleId = JTB_Global_Modules::save($moduleData);

    if ($moduleId === false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to save module']);
        exit;
    }

    // Get updated module
    $module = JTB_Global_Modules::get($moduleId);

    echo json_encode([
        'success' => true,
        'module_id' => $moduleId,
        'module' => $module,
        'message' => empty($data['id']) ? 'Module saved to library' : 'Module updated'
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
