<?php
require_once __DIR__ . '/../../core/AdminController.php';

class ThemeBuilder extends AdminController {
    private $themePresets = [
        'light' => 'Light',
        'dark' => 'Dark',
        'corporate' => 'Corporate',
        'modern' => 'Modern'
    ];

    public function index() {
        $this->checkAdminAccess();
        
        $presetsPath = __DIR__ . '/../../themes/presets/';
        $availableThemes = [];
        
        foreach ($this->themePresets as $key => $name) {
            if (file_exists($presetsPath . $key . '.json')) {
                $availableThemes[$key] = $name;
            }
        }

        require_once __DIR__ . '/theme-builder-view.php';
    }
}

$themeBuilder = new ThemeBuilder();
$themeBuilder->index();
