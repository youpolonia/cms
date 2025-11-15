<?php
class PluginManager {
    protected static $installedPlugins = [];
    protected static $pluginDir = __DIR__ . '/../plugins/';
    protected static $installedDir = __DIR__ . '/../plugins/installed/';

    public static function installPlugin($slug) {
        $pluginPath = self::$pluginDir . $slug;
        $pluginJson = $pluginPath . '/plugin.json';
        
        if (!is_dir($pluginPath)) {
            throw new \RuntimeException("Plugin directory not found: $slug");
        }

        if (!file_exists($pluginJson)) {
            throw new \RuntimeException("Missing plugin.json for: $slug");
        }

        $pluginData = json_decode(file_get_contents($pluginJson), true);
        if (!$pluginData) {
            throw new \RuntimeException("Invalid plugin.json for: $slug");
        }

        // Create installed directory if needed
        if (!is_dir(self::$installedDir)) {
            mkdir(self::$installedDir, 0755, true);
        }

        // Copy plugin to installed directory
        self::copyDirectory($pluginPath, self::$installedDir . $slug);

        // Register plugin
        self::$installedPlugins[$slug] = $pluginData;

        return true;
    }

    public static function uninstallPlugin($slug) {
        $pluginPath = self::$installedDir . $slug;
        
        if (!is_dir($pluginPath)) {
            throw new \RuntimeException("Plugin not installed: $slug");
        }

        // Remove plugin files
        self::removeDirectory($pluginPath);

        // Unregister plugin
        unset(self::$installedPlugins[$slug]);

        return true;
    }

    protected static function copyDirectory($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);
        
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                if (is_dir($src . '/' . $file)) {
                    self::copyDirectory($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        
        closedir($dir);
    }

    protected static function removeDirectory($dir) {
        if (!is_dir($dir)) return;
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? self::removeDirectory($path) : unlink($path);
        }
        
        return rmdir($dir);
    }
}
