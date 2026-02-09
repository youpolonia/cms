<?php
/**
 * Modules API Endpoint
 * GET /api/jtb/modules
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

header('Content-Type: application/json');

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Authentication is already checked in router.php

// Build modules list
$modules = [];

foreach (JTB_Registry::getInstances() as $slug => $module) {
    // Get default styles for this module type
    $defaultAttrs = JTB_Default_Styles::getDefaults($slug);

    $modules[$slug] = [
        'slug' => $module->getSlug(),
        'name' => $module->getName(),
        'icon' => $module->icon,
        'category' => $module->category,
        'is_child' => $module->is_child,
        'child_slug' => $module->child_slug,
        'defaults' => $defaultAttrs,
        'fields' => [
            'content' => $module->getContentFields(),
            'design' => $module->getDesignFields(),
            'advanced' => $module->getAdvancedFields()
        ]
    ];
}

// Return response
echo json_encode([
    'success' => true,
    'data' => [
        'modules' => $modules,
        'categories' => JTB_Registry::getCategories(),
        'count' => JTB_Registry::count()
    ]
]);
