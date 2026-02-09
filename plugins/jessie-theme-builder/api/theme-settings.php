<?php
/**
 * JTB Theme Settings API Endpoint
 * Handles GET and POST requests for global theme settings
 *
 * @package JessieThemeBuilder
 * @updated 2026-02-03 - Added Style System integration
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

// Load required classes
$pluginPath = dirname(__DIR__);
if (!class_exists(__NAMESPACE__ . '\\JTB_Theme_Settings')) {
    require_once $pluginPath . '/includes/class-jtb-theme-settings.php';
}
if (!class_exists(__NAMESPACE__ . '\\JTB_CSS_Generator')) {
    require_once $pluginPath . '/includes/class-jtb-css-generator.php';
}
if (!class_exists(__NAMESPACE__ . '\\JTB_Style_System')) {
    require_once $pluginPath . '/includes/class-jtb-style-system.php';
}

// Ensure table exists
JTB_Theme_Settings::createTable();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Get all settings
        $settings = JTB_Theme_Settings::getAll();
        $defaults = JTB_Theme_Settings::getDefaults();

        echo json_encode([
            'success' => true,
            'settings' => $settings,
            'defaults' => $defaults
        ]);

    } elseif ($method === 'POST') {
        // Save settings
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON data: ' . json_last_error_msg());
        }

        // Validate structure - expecting grouped settings
        if (!is_array($data)) {
            throw new \Exception('Settings must be an array');
        }

        // Save all settings
        if (JTB_Theme_Settings::saveAll($data)) {
            // Invalidate Style System cache
            JTB_Style_System::clearCache();

            // Regenerate CSS file
            $cssPath = JTB_CSS_Generator::generateCssFile($data);

            // Get fresh CSS variables from Style System
            $styleSystem = JTB_Style_System::getInstance();
            $cssVariables = $styleSystem->generateCssVariables();

            echo json_encode([
                'success' => true,
                'message' => 'Settings saved successfully',
                'css_path' => $cssPath,
                'css_variables_length' => strlen($cssVariables)
            ]);
        } else {
            throw new \Exception('Failed to save settings');
        }

    } else {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => 'Method not allowed'
        ]);
    }

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
