<?php
/**
 * Plugin Block Registry - Manages plugin-provided content blocks
 */
class PluginBlockRegistry {
    private static $instance;
    private $blocks = [];
    
    private function __construct() {
        // Scan plugins directory for block definitions
        $this->scanPluginBlocks();
    }
    
    public static function getInstance(): self {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function scanPluginBlocks(): void {
        $pluginDirs = glob(PLUGINS_DIR . '/*', GLOB_ONLYDIR);
        
        foreach ($pluginDirs as $pluginDir) {
            $blocksDir = "$pluginDir/blocks";
            if (!is_dir($blocksDir)) continue;
            
            $blockFiles = glob("$blocksDir/*.json");
            foreach ($blockFiles as $blockFile) {
                $blockData = json_decode(file_get_contents($blockFile), true);
                if ($blockData) {
                    $this->registerBlock($blockData);
                }
            }
        }
    }
    
    public function registerBlock(array $blockDef): void {
        $this->blocks[$blockDef['type']] = $blockDef;
    }
    
    public function getBlockTypes(): array {
        return array_keys($this->blocks);
    }
    
    public function getBlockDefinition(string $type): ?array {
        return $this->blocks[$type] ?? null;
    }
    
    public function getBlockSchema(string $type): ?array {
        return $this->blocks[$type]['schema'] ?? null;
    }
}
