<?php
class Component {
    protected $db;
    
    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function fetchComponents() {
        $stmt = $this->db->query("SELECT id, name, icon FROM page_builder_components");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function renderComponentPalette($components) {
        $html = '';
        foreach ($components as $component) {
            $html .= <<<HTML
            <div class="component" draggable="true" data-component-id="{$component['id']}">
                <span class="component-icon">{$component['icon']}</span>
                <h4>{$this->sanitizeHTML($component['name'])}</h4>
            </div>
HTML;
        }
        return $html;
    }

    private function sanitizeHTML($input) {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
}
