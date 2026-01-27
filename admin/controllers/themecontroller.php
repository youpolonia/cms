<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../../core/csrf.php';

/**
 * Theme Controller - Handles theme management in admin
 */
require_once __DIR__ . '/../../themes/ThemeBuilder.php';

class ThemeController {
    /**
     * Show import form
     */
    public function importForm() {
        require_once __DIR__ . '/../views/themes/import.php';
    }

    /**
     * List all available themes
     */
    public function list() {
        $themes = ThemeRegistry::getAll();
        require_once __DIR__ . '/../views/themes/list.php';
    }

    /**
     * Preview a theme
     */
    public function preview(string $themeName) {
        $theme = ThemeRegistry::get($themeName);
        foreach (ThemeBuilder::getCspHeaders() as $header => $value) {
            header("$header: $value");
        }
        require_once __DIR__ . '/../views/themes/preview.php';
    }

    /**
     * Edit theme configuration
     */
    public function edit(string $themeName) {
        $theme = ThemeRegistry::get($themeName);
        require_once __DIR__ . '/../views/themes/editor.php';
    }

    /**
     * Activate/deactivate theme
     */
    public function toggle(string $themeName, bool $active) {
        ThemeRegistry::setActive($themeName, $active);
        header('Location: /admin/themes');
    }

    /**
     * Export theme as JSON
     */
    public function export(string $themeName) {
        $theme = ThemeRegistry::get($themeName);
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="'.$themeName.'.json"');
        echo json_encode($theme, JSON_PRETTY_PRINT);
    }

    /**
     * Import theme from JSON
     */
    public function import() {
        csrf_validate_or_403();
        if ($_FILES['theme_file']['error'] === UPLOAD_ERR_OK) {
            $json = file_get_contents($_FILES['theme_file']['tmp_name']);
            $data = json_decode($json, true);
            
            if ($data && ThemeLoader::validateStructure($data)) {
                ThemeRegistry::register($data['meta']['name'], $data);
                $_SESSION['message'] = 'Theme imported successfully';
            } else {
                $_SESSION['error'] = 'Invalid theme file structure';
            }
        }
        header('Location: /admin/themes');
    }

    /**
     * Save theme changes
     */
    public function save(string $themeName) {
        csrf_validate_or_403();
        if (!empty($_POST['theme_json'])) {
            $data = json_decode($_POST['theme_json'], true);
            
            if ($data && ThemeLoader::validateStructure($data)) {
                ThemeRegistry::register($themeName, $data);
                $_SESSION['message'] = 'Theme saved successfully';
            } else {
                $_SESSION['error'] = 'Invalid theme configuration';
            }
        }
        header('Location: /admin/themes/edit/' . $themeName);
    }
}
