<?php
namespace Core;

class PluginSDK {
    private $manifest;
    private $pluginPath;
    private $sandboxEnabled = true;
    private $eventBus;

    public function __construct(string $pluginPath) {
        $this->pluginPath = $pluginPath;
        $this->eventBus = EventBus::getInstance();
        $this->validateManifest();
        $this->registerEventListeners();
        $this->registerBlocks();
    }

    private function validateManifest(): void {
        $manifestPath = $this->pluginPath . '/plugin.json';
        if (!file_exists($manifestPath)) {
            throw new \RuntimeException("Plugin manifest not found");
        }
        $this->manifest = json_decode(file_get_contents($manifestPath), true);
    }

    private function registerBlocks(): void {
        if (!isset($this->manifest['blocks'])) {
            return;
        }

        foreach ($this->manifest['blocks'] as $blockName => $blockDef) {
            try {
                PluginBlockRegistry::registerBlock(
                    $this->manifest['name'],
                    array_merge(['name' => $blockName], $blockDef)
                );
            } catch (\Throwable $e) {
                error_log("Failed to register block {$blockName}: " . $e->getMessage());
            }
        }
    }

    // ... rest of existing PluginSDK methods remain unchanged ...
}
