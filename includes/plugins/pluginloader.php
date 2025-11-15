<?php
/**
 * PluginLoader - Static plugin management for FTP-only CMS
 */
class PluginLoader {
    /**
     * Load all plugins from plugins directory
     * @return array List of loaded plugin manifests
     */
    public static function loadAll(): array {
        $plugins = [];
        $pluginDirs = glob(__DIR__ . '/../../plugins/*', GLOB_ONLYDIR);
        
        foreach ($pluginDirs as $dir) {
            $manifest = self::loadManifest($dir);
            if ($manifest) {
                $plugins[basename($dir)] = $manifest;
            }
        }
        
        return $plugins;
    }

    /**
     * Load plugin manifest
     * @param string $pluginDir Path to plugin directory
     * @return array|null Manifest data or null if invalid
     */
    public static function loadManifest(string $pluginDir): ?array {
        $manifestFile = $pluginDir . '/manifest.json';
        
        if (!file_exists($manifestFile)) {
            return null;
        }
        
        $manifest = json_decode(file_get_contents($manifestFile), true);
        
        if (!self::validateManifest($manifest)) {
            return null;
        }
        
        return $manifest;
    }

    /**
     * Validate plugin manifest structure
     * @param array $manifest Manifest data
     * @return bool True if valid
     */
    private static function validateManifest(array $manifest): bool {
        $required = ['name', 'version'];
        foreach ($required as $field) {
            if (!isset($manifest[$field])) {
                return false;
            }
        }
        
        // Validate version format (semver)
        if (!preg_match('/^\d+\.\d+\.\d+$/', $manifest['version'])) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if plugin has AI features
     * @param array $manifest Plugin manifest
     * @return bool True if AI features declared
     */
    public static function hasAIFeatures(array $manifest): bool {
        return !empty($manifest['ai_features']);
    }

    /**
     * Register AI analyzer for plugin
     * @param string $pluginName Plugin identifier
     * @param string $analyzerClass Fully qualified analyzer class name
     * @return bool True if registration succeeded
     */
    public static function registerAnalyzer(string $pluginName, string $analyzerClass): bool {
        if (!class_exists($analyzerClass)) {
            return false;
        }
        
        AIClient::registerAnalyzer($pluginName, $analyzerClass);
        return true;
    }

    /**
     * Register AI transformer for plugin
     * @param string $pluginName Plugin identifier
     * @param string $transformerClass Fully qualified transformer class name
     * @return bool True if registration succeeded
     */
    public static function registerTransformer(string $pluginName, string $transformerClass): bool {
        if (!class_exists($transformerClass)) {
            return false;
        }
        
        return AIClient::registerTransformer($pluginName, $transformerClass);
    }
}
