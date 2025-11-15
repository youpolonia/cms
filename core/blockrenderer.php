<?php
/**
 * Block Renderer - Handles secure rendering of content blocks
 */
class BlockRenderer {
    private $sandbox;
    
    public function __construct() {
        $this->sandbox = new PluginSandbox();
    }
    
    public function renderEdit(string $type, array $data): string {
        $registry = PluginBlockRegistry::getInstance();
        $blockDef = $registry->getBlockDefinition($type);
        
        if (!$blockDef) {
            return '<div class="block-error">Invalid block type</div>';
        }
        
        return $this->sandbox->execute(function() use ($blockDef, $data) {
            ob_start();
            require_once $blockDef['edit_template'];
            return ob_get_clean();
        });
    }
    
    public function renderPreview(string $type, array $data): string {
        $registry = PluginBlockRegistry::getInstance();
        $blockDef = $registry->getBlockDefinition($type);
        
        if (!$blockDef) {
            return '<div class="block-error">Invalid block type</div>';
        }
        
        return $this->sandbox->execute(function() use ($blockDef, $data) {
            ob_start();
            require_once $blockDef['preview_template'];
            return ob_get_clean();
        });
    }
    
    public function renderConfigUI(string $type): string {
        $registry = PluginBlockRegistry::getInstance();
        $schema = $registry->getBlockSchema($type);
        
        if (!$schema) {
            return '<div class="config-error">No configuration available</div>';
        }
        
        return $this->generateFormFromSchema($schema);
    }
    
    private function generateFormFromSchema(array $schema): string {
        // Implementation would generate form HTML from JSON schema
        // This is a simplified placeholder
        $html = '<div class="block-config">';
        foreach ($schema['properties'] as $name => $prop) {
            $html .= sprintf(
                '<div class="form-group"><label>%s</label><input type="%s" name="%s"></div>',
                htmlspecialchars($prop['title'] ?? $name),
                $this->mapSchemaTypeToInput($prop['type']),
                htmlspecialchars($name)
            );
        }
        $html .= '</div>';
        return $html;
    }
    
    private function mapSchemaTypeToInput(string $type): string {
        $map = [
            'string' => 'text',
            'number' => 'number',
            'boolean' => 'checkbox'
        ];
        return $map[$type] ?? 'text';
    }
}