<?php
namespace CMS\Plugins;

use Core\Cache\CacheInterface;
use Core\Cache\FileCache;

class EnhancedPluginLoader extends PluginLoader {
    private CacheInterface $cache;
    private array $dependencyGraph = [];
    private array $versionChecks = [];

    public function __construct(?CacheInterface $cache = null) {
        $this->cache = $cache ?? new FileCache();
        parent::__construct();
    }

    public function loadAll(): void {
        $pluginDirs = glob(__DIR__.'/*', GLOB_ONLYDIR);
        $plugins = [];
        
        // First pass: collect plugin info and check requirements
        foreach ($pluginDirs as $dir) {
            $pluginFile = $dir.'/plugin.php';
            if (file_exists($pluginFile)) {
                $pluginBase = realpath(__DIR__);
                $pluginTarget = realpath($pluginFile);
                if (!$pluginTarget || !str_starts_with($pluginTarget, $pluginBase . DIRECTORY_SEPARATOR) || !is_file($pluginTarget)) {
                    error_log("SECURITY: blocked dynamic include: plugin.php");
                    continue;
                }
                require_once $pluginTarget;
                
                $pluginName = basename($dir);
                $className = $pluginName.'\\'.$pluginName.'Plugin';
                
                if (class_exists($className)) {
                    $plugin = new $className(self::getHookManager());
                    
                    if ($plugin instanceof PluginInterface) {
                        $plugins[$pluginName] = $plugin;
                        
                        // Handle enhanced plugins
                        if ($plugin instanceof EnhancedPluginInterface) {
                            $this->processEnhancedPlugin($pluginName, $plugin);
                        }
                    }
                }
            }
        }

        // Resolve dependencies and load in correct order
        $sortedPlugins = $this->resolveDependencies($plugins);
        
        foreach ($sortedPlugins as $pluginName => $plugin) {
            $this->loadPlugin($plugin);
        }
    }

    private function processEnhancedPlugin(string $name, EnhancedPluginInterface $plugin): void {
        // Store dependencies
        $this->dependencyGraph[$name] = $plugin->getDependencies();
        
        // Store version requirements
        $this->versionChecks[$name] = $plugin->getVersionCompatibility();
    }

    private function resolveDependencies(array $plugins): array {
        $resolved = [];
        $unresolved = [];
        
        foreach (array_keys($plugins) as $pluginName) {
            $this->resolvePluginDependencies($pluginName, $plugins, $resolved, $unresolved);
        }
        
        return array_intersect_key($plugins, array_flip($resolved));
    }

    private function resolvePluginDependencies(
        string $pluginName, 
        array $plugins, 
        array &$resolved, 
        array &$unresolved
    ): void {
        $unresolved[$pluginName] = true;
        
        foreach ($this->dependencyGraph[$pluginName] ?? [] as $dep => $version) {
            if (!isset($plugins[$dep])) {
                throw new \RuntimeException("Missing required plugin: $dep");
            }
            
            if (!isset($resolved[$dep])) {
                if (isset($unresolved[$dep])) {
                    throw new \RuntimeException("Circular dependency detected: $pluginName -> $dep");
                }
                $this->resolvePluginDependencies($dep, $plugins, $resolved, $unresolved);
            }
        }
        
        $resolved[$pluginName] = true;
        unset($unresolved[$pluginName]);
    }

    private function loadPlugin(PluginInterface $plugin): void {
        $hookManager = self::getHookManager();
        
        // Handle enhanced plugins
        if ($plugin instanceof EnhancedPluginInterface) {
            $this->handleLifecycle($plugin);
            $plugin->registerHooks();
            
            // Register hook points with priorities
            foreach ($plugin->getHookPoints() as $hook => $priority) {
                $hookManager->addHookPoint($hook, $priority);
            }
        } else {
            // Legacy plugin support
            $plugin->init();
            $plugin->registerHooks();
        }
        
        $this->loadedPlugins[] = $plugin;
        // Also update registry
        self::getRegistry()->register($plugin);
    }

    private function handleLifecycle(EnhancedPluginInterface $plugin): void {
        $plugin->init();
        
        // Check if plugin needs installation
        if (!$this->cache->has('plugin_installed_'.get_class($plugin))) {
            $plugin->install();
            $this->cache->set('plugin_installed_'.get_class($plugin), true);
        }
        
        // Activate plugin
        $plugin->activate();
    }
}
