<?php
/**
 * JTB API - Get Condition Types
 * GET /api/jtb/conditions
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

header('Content-Type: application/json');

// Authentication is already checked in router.php

// Only GET allowed
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    // Get page types with labels
    $pageTypes = JTB_Template_Conditions::getPageTypes();

    // Format for frontend
    $types = [];
    foreach ($pageTypes as $value => $config) {
        $types[] = [
            'value' => $value,
            'label' => $config['label'],
            'has_objects' => $config['has_objects'],
            'object_label' => $config['object_label'] ?? null,
            'priority' => $config['priority']
        ];
    }

    echo json_encode([
        'success' => true,
        'types' => $types
    ]);

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
