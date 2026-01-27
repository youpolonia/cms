<?php
/**
 * Widget Layout Controller
 * Handles widget positioning and visibility rules
 */

// Security check
defined('CMS_ADMIN') or die('Unauthorized access');

class WidgetLayoutController {
    private static $layoutFile = __DIR__.'/data/layouts.json';
    
    /**
     * Get current widget layout
     */
    public static function getLayout() {
        if (!file_exists(self::$layoutFile)) {
            return ['widgets' => [], 'rules' => []];
        }
        
        $data = file_get_contents(self::$layoutFile);
        return json_decode($data, true) ?: ['widgets' => [], 'rules' => []];
    }

    /**
     * Save widget layout
     */
    public static function saveLayout(array $layout) {
        // Validate input
        if (!isset($layout['widgets']) || !isset($layout['rules'])) {
            throw new InvalidArgumentException('Invalid layout structure');
        }

        // Create directory if needed
        if (!is_dir(dirname(self::$layoutFile))) {
            mkdir(dirname(self::$layoutFile), 0755, true);
        }

        file_put_contents(self::$layoutFile, json_encode($layout, JSON_PRETTY_PRINT));
        return true;
    }

    /**
     * Get visibility rules for a widget
     */
    public static function getWidgetRules(string $widgetId) {
        $layout = self::getLayout();
        return $layout['rules'][$widgetId] ?? [];
    }

    /**
     * Render layout editor UI
     */
    public static function renderEditor() {
        $regions = [
            ['id' => 'header', 'name' => 'Header'],
            ['id' => 'sidebar', 'name' => 'Sidebar'],
            ['id' => 'footer', 'name' => 'Footer']
        ];
        
        $layout = self::getLayout();
        require_once __DIR__.'/views/layout_editor.php';
    }

    /**
     * Handle AJAX save request
     */
    public static function handleSave() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                throw new InvalidArgumentException('Invalid input data');
            }

            $result = self::saveLayout([
                'widgets' => $input['widgets'],
                'rules' => $input['rules']
            ]);

            header('Content-Type: application/json');
            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
