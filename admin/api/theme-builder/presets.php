<?php
/**
 * Theme Builder Presets API
 * 
 * Location: /admin/api/theme-builder/presets.php
 * 
 * Endpoints:
 * GET /admin/api/theme-builder/presets.php           - List all presets
 * GET /admin/api/theme-builder/presets.php?type=X    - List presets by type
 * GET /admin/api/theme-builder/presets.php?id=X      - Get single preset
 *
 * @package ThemeBuilder
 * @version 3.0
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__, 3));
}

require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/database.php';
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

require_once CMS_ROOT . '/admin/includes/permissions.php';
if (!cms_get_current_admin()) {
    http_response_code(401);
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

require_once CMS_ROOT . '/core/theme-builder/init.php';

header('Content-Type: application/json');

// Get single preset by ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $presetId = (int)$_GET['id'];
    $preset = tb_get_preset($presetId);
    
    if ($preset) {
        echo json_encode(['success' => true, 'preset' => $preset]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Preset not found']);
    }
    exit;
}

// Get presets by type or all
$type = $_GET['type'] ?? null;
$validTypes = ['header', 'footer', 'sidebar', '404', 'archive', 'single'];

if ($type && !in_array($type, $validTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid type']);
    exit;
}

if ($type) {
    // Get presets for specific type
    $presets = tb_get_presets($type);
} else {
    // Get all presets (flattened)
    $grouped = tb_get_all_presets();
    $presets = [];
    foreach ($grouped as $typePresets) {
        $presets = array_merge($presets, $typePresets);
    }
}

echo json_encode([
    'success' => true, 
    'presets' => $presets,
    'count' => count($presets)
]);
