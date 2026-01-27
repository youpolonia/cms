<?php
/**
 * Plugin Manager - Handles plugin lifecycle
 */
class PluginManager {
    private HookSystem $hookSystem;
    private array $plugins = [];
    private string $pluginsDir = __DIR__ . '/plugins';

    public function __construct(HookSystem $hookSystem) {
        $this->hookSystem = $hookSystem;
    }

    /**
     * Discover and load all valid plugins
     */
    public function loadPlugins(): void {
        if (!is_dir($this->pluginsDir)) {
            mkdir($this->pluginsDir, 0755, true);
            return;
        }

        foreach (scandir($this->pluginsDir) as $pluginDir) {
            if ($pluginDir === '.' || $pluginDir === '..') continue;
            
            $pluginPath = $this->pluginsDir . '/' . $pluginDir;
            if (is_dir($pluginPath)) {
                $this->loadPlugin($pluginDir);
            }
        }
    }

    /**
     * Load a single plugin
     */
    private function loadPlugin(string $pluginDir): bool {
        $pluginFile = $this->pluginsDir . '/' . $pluginDir . '/plugin.php';
        if (!file_exists($pluginFile)) {
            return false;
        }

        try {
            $pluginInfo = $this->getPluginInfo($pluginFile);
            if (!$this->checkCompatibility($pluginInfo)) {
                return false;
            }

            require_once $pluginFile;
            $this->plugins[$pluginDir] = $pluginInfo;
            return true;
        } catch (Throwable $e) {
            error_log("Plugin load failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get plugin metadata
     */
    private function getPluginInfo(string $pluginFile): array {
        $contents = file_get_contents($pluginFile);
        preg_match('/Plugin Name:\s*(.+)/i', $contents, $name);
        preg_match('/Version:\s*(.+)/i', $contents, $version);
        preg_match('/Requires CMS:\s*(.+)/i', $contents, $requires);

        return [
            'name' => $name[1] ?? 'Unknown',
            'version' => $version[1] ?? '1.0',
            'requires' => $requires[1] ?? '*',
            'active' => true
        ];
    }

    /**
     * Check plugin compatibility
     */
    private function checkCompatibility(array $pluginInfo): bool {
        // Basic version check - can be expanded
        return $pluginInfo['requires'] === '*' || 
               version_compare(CMS_VERSION, $pluginInfo['requires'], '>=');
    }
}
