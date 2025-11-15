<?php
/**
 * SettingsTrait - Provides common settings management functionality
 */
require_once __DIR__ . '/../../core/csrf.php';
csrf_boot('admin');
trait SettingsTrait
{
    protected $db;

    /**
     * Get settings for a plugin
     */
    public function getPluginSettings(string $pluginId): array
    {
        $stmt = $this->db->prepare("
            SELECT settings 
            FROM plugin_settings 
            WHERE plugin_id = ?
        ");
        $stmt->execute([$pluginId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? json_decode($result['settings'], true) : [];
    }

    /**
     * Update plugin settings
     */
    public function updatePluginSettings(string $pluginId, array $settings): bool
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_validate_or_403(); }
        $stmt = $this->db->prepare("
            INSERT INTO plugin_settings 
            (plugin_id, settings) 
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE settings = ?
        ");
        
        $jsonSettings = json_encode($settings);
        return $stmt->execute([$pluginId, $jsonSettings, $jsonSettings]);
    }

    /**
     * Merge new settings with existing ones
     */
    public function mergePluginSettings(string $pluginId, array $newSettings): bool
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_validate_or_403(); }
        $current = $this->getPluginSettings($pluginId);
        $merged = array_merge($current, $newSettings);
        return $this->updatePluginSettings($pluginId, $merged);
    }

    /**
     * Reset settings to defaults
     */
    public function resetPluginSettings(string $pluginId): bool
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_validate_or_403(); }
        $defaults = $this->getDefaultSettings($pluginId);
        return $this->updatePluginSettings($pluginId, $defaults);
    }

    /**
     * Get default settings from plugin config
     */
    protected function getDefaultSettings(string $pluginId): array
    {
        $pluginPath = $this->pluginsDir . $pluginId . '/';
        $configFile = $pluginPath . 'config/settings.json';
        
        if (file_exists($configFile)) {
            return json_decode(file_get_contents($configFile), true);
        }
        
        return [];
    }
}
