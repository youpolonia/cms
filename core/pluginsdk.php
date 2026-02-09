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

    private function registerEventListeners(): void {
        // Register event listeners from plugin manifest
        if (!isset($this->manifest['events'])) {
            return;
        }
        foreach ($this->manifest['events'] as $event => $handler) {
            $this->eventBus->listen($event, function($data = null) use ($handler) {
                if (is_callable($handler)) {
                    call_user_func($handler, $data);
                }
            });
        }
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

    public function load(): void {
        // Plugin loaded via bootstrap.php if exists
    }

    public function getManifest(): array {
        return $this->manifest ?? [];
    }

    public function getPluginPath(): string {
        return $this->pluginPath;
    }
}
