<?php
require_once __DIR__ . '/pluginhealthmonitor.php';
require_once __DIR__ . '/../../core/csrf.php';
/**
 * Basic Plugin Update Handler
 */

csrf_boot('admin');
class PluginUpdater {
    /**
     * Check for plugin updates
     * @param string $pluginId Plugin identifier
     * @param string $currentVersion Current version
     * @param string $latestVersion Latest available version
     * @return bool True if update available
     */
    public static function checkUpdate(string $pluginId, string $currentVersion, string $latestVersion): bool {
        return version_compare($currentVersion, $latestVersion, '<');
    }

    /**
     * Perform plugin update
     * @param string $pluginId Plugin identifier
     * @param array $updateData Update package data
     * @return bool True if successful
     */
    public static function update(string $pluginId, array $updateData): bool {
        global $db;
        
        try {
            // Trigger deactivation hook before update
            PluginHooks::trigger('deactivation', $pluginId);

            // Update plugin files (simplified - actual implementation would handle file updates)
            // ...

            // Update version in settings
            $stmt = $db->prepare("UPDATE plugin_settings SET settings = JSON_SET(settings, '$.version', ?) WHERE plugin_id = ?");
            $stmt->bind_param("ss", $updateData['version'], $pluginId);
            $result = $stmt->execute();

            // Trigger activation hook after update
            PluginHooks::trigger('activation', $pluginId);

            // Log successful update
            $monitor = PluginHealthMonitor::getInstance();
            $monitor->logUpdate($pluginId, $updateData['version']);
            
            return $result;
        } catch (Exception $e) {
            error_log("Plugin update failed: {$e->getMessage()}");
            return false;
        }
    }
}
