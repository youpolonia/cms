<?php
namespace CMS\Plugins;

/**
 * CMS Plugin Registry
 * Handles plugin registration, activation, and dependency management
 */
class PluginRegistry {
    private $registeredPlugins = [];
    private $activePlugins = [];
    protected $hookManager;

    public function __construct(HookManager $hookManager) {
        $this->hookManager = $hookManager;
    }

    protected function getHookManager(): HookManager {
        return $this->hookManager;
    }

    /**
     * Register a plugin
     * @param string $pluginName Unique plugin identifier
     * @param array $metadata Plugin metadata
     * @param array $dependencies Required plugins
     */
    public function registerPlugin(
        string $pluginName,
        array $metadata,
        array $dependencies = []
    ): void {
        $this->registeredPlugins[$pluginName] = [
            'metadata' => $metadata,
            'dependencies' => $dependencies,
            'active' => false
        ];
    }

    /**
     * Activate a plugin
     * @param string $pluginName Plugin to activate
     * @throws \RuntimeException If dependencies aren't met
     */
    public function activatePlugin(string $pluginName): void {
        if (!isset($this->registeredPlugins[$pluginName])) {
            throw new \RuntimeException("Plugin $pluginName not registered");
        }

        // Check dependencies
        foreach ($this->registeredPlugins[$pluginName]['dependencies'] as $dep) {
            if (!isset($this->activePlugins[$dep])) {
                throw new \RuntimeException(
                    "Missing dependency: $dep required by $pluginName"
                );
            }
        }

        $this->activePlugins[$pluginName] = true;
        $this->registeredPlugins[$pluginName]['active'] = true;
    }

    /**
     * Deactivate a plugin
     * @param string $pluginName Plugin to deactivate
     */
    public function deactivatePlugin(string $pluginName): void {
        if (isset($this->activePlugins[$pluginName])) {
            unset($this->activePlugins[$pluginName]);
            $this->registeredPlugins[$pluginName]['active'] = false;
        }
    }

    /**
     * Get all registered plugins
     * @return array
     */
    public function getRegisteredPlugins(): array {
        return $this->registeredPlugins;
    }

    /**
     * Get active plugins
     * @return array
     */
    public function getActivePlugins(): array {
        return array_filter($this->registeredPlugins, function($plugin) {
            return $plugin['active'];
        });
    }

    /**
     * Check if plugin is active
     * @param string $pluginName
     * @return bool
     */
    public function isPluginActive(string $pluginName): bool {
        return isset($this->activePlugins[$pluginName]);
    }
}
