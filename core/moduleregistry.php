<?php
namespace Core;

class ModuleRegistry {
    private static $modules = [];
    private static $plugins = [];
    private static $hooks = [];
    private static $filters = [];

    public static function register(string $moduleName, string $moduleClass): void {
        self::$modules[$moduleName] = $moduleClass;
    }

    public static function registerPlugin(string $pluginPath): void {
        try {
            // Validate plugin.json exists
            $manifestFile = $pluginPath . '/plugin.json';
            if (!file_exists($manifestFile)) {
                throw new \RuntimeException("Missing plugin.json in $pluginPath");
            }

            // Validate manifest structure
            $manifest = json_decode(file_get_contents($manifestFile), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException("Invalid plugin.json format in $pluginPath");
            }

            // Validate required fields
            $required = ['name', 'slug', 'version', 'author'];
            foreach ($required as $field) {
                if (empty($manifest[$field])) {
                    throw new \RuntimeException("Missing required field '$field' in plugin.json");
                }
            }

            $plugin = new PluginSDK($pluginPath);
            self::$plugins[$plugin->getManifest()['name']] = $plugin;

            // Load plugin hooks
            $bootstrapFile = $pluginPath . '/bootstrap.php';
            if (file_exists($bootstrapFile)) {
                $hookRegister = require_once $bootstrapFile;
                if (is_callable($hookRegister)) {
                    $hookRegister(self::class);
                }
            }

            $plugin->load();
        } catch (\Throwable $e) {
            error_log("Plugin registration failed: " . $e->getMessage());
        }
    }

    public static function addHook(string $name, callable $callback): void {
        self::$hooks[$name][] = $callback;
    }

    public static function addFilter(string $name, callable $callback): void {
        self::$filters[$name][] = $callback;
    }

    public static function get(string $moduleName): ?string {
        return self::$modules[$moduleName] ?? null;
    }

    public static function getAll(): array {
        return array_merge(self::$modules, self::$plugins);
    }

    public static function loadModuleRoutes(string $modulePath): void {
        $routesFile = $modulePath . '/routes.php';
        if (file_exists($routesFile)) {
            require_once $routesFile;
        }
    }
}
