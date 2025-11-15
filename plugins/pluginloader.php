<?php
namespace CMS\Plugins;

/**
 * CMS Plugin Loader
 * Handles plugin discovery, loading and management
 */
class PluginLoader {
    private static ?HookManager $hookManager = null;
    private static ?PluginRegistry $pluginRegistry = null;
    protected array $loadedPlugins = [];

    public static function getHookManager(): HookManager {
        if (self::$hookManager === null) {
            self::$hookManager = new HookManager();
        }
        return self::$hookManager;
    }

    public static function getRegistry(): PluginRegistry {
        if (self::$pluginRegistry === null) {
            self::$pluginRegistry = new PluginRegistry();
        }
        return self::$pluginRegistry;
    }

    public function loadAll(): void {
        $pluginDirs = glob(__DIR__.'/*', GLOB_ONLYDIR);

        foreach ($pluginDirs as $dir) {
            $pluginFile = $dir.'/plugin.php';
            if (file_exists($pluginFile)) {
                $pluginBase = realpath(__DIR__);
                $pluginTarget = realpath($pluginFile);
                if (!$pluginTarget || !str_starts_with($pluginTarget, $pluginBase . DIRECTORY_SEPARATOR) || !is_file($pluginTarget)) {
                    error_log("SECURITY: blocked dynamic include: plugin.php");
                    continue;
                }
                require_once $pluginFile;

                $pluginName = basename($dir);
                $className = $pluginName.'\\'.$pluginName.'Plugin';
                
                if (class_exists($className)) {
                    $hookManager = self::getHookManager();
                    $plugin = new $className($hookManager);
                    
                    if ($plugin instanceof PluginInterface) {
                        $this->loadedPlugins[] = $plugin;
                        $plugin->init();
                        $plugin->registerHooks();
                    }
                }
            }
        }
    }

    public function getLoadedPlugins(): array {
        return $this->loadedPlugins;
    }
}
