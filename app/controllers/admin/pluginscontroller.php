<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;
use Core\Database;

class PluginsController
{
    private string $pluginsDir;
    private string $configFile;

    public function __construct()
    {
        $this->pluginsDir = \CMS_ROOT . '/plugins';
        $this->configFile = \CMS_ROOT . '/config/installed_plugins.json';
    }

    /**
     * Plugins Marketplace - main listing
     */
    public function index(Request $request): void
    {
        $installedPlugins = $this->getInstalledPlugins();
        $availablePlugins = $this->scanAvailablePlugins();

        render('admin/plugins/index', [
            'installed' => $installedPlugins,
            'available' => $availablePlugins,
            'pluginsDir' => $this->pluginsDir,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    /**
     * Install a plugin
     */
    public function install(Request $request): void
    {
        $pluginSlug = $request->post('plugin');
        
        if (!$pluginSlug) {
            Session::setFlash('error', 'No plugin specified');
            Response::redirect('/admin/plugins');
            return;
        }

        $pluginPath = $this->pluginsDir . '/' . $pluginSlug;
        
        if (!is_dir($pluginPath)) {
            Session::setFlash('error', 'Plugin not found');
            Response::redirect('/admin/plugins');
            return;
        }

        // Load plugin manifest
        $manifestFile = $pluginPath . '/plugin.json';
        if (!file_exists($manifestFile)) {
            Session::setFlash('error', 'Invalid plugin: missing plugin.json');
            Response::redirect('/admin/plugins');
            return;
        }

        $manifest = json_decode(file_get_contents($manifestFile), true);
        
        // Add to installed plugins
        $installed = $this->getInstalledPlugins();
        $installed[$pluginSlug] = [
            'name' => $manifest['name'] ?? $pluginSlug,
            'version' => $manifest['version'] ?? '1.0.0',
            'enabled' => true,
            'installed_at' => date('Y-m-d H:i:s')
        ];
        
        $this->saveInstalledPlugins($installed);

        // Run install hook if exists
        $installFile = $pluginPath . '/install.php';
        if (file_exists($installFile)) {
            require_once $installFile;
        }

        Session::setFlash('success', "Plugin '{$manifest['name']}' installed successfully");
        Response::redirect('/admin/plugins');
    }

    /**
     * Toggle plugin enabled/disabled
     */
    public function toggle(Request $request, int $id): void
    {
        // $id is actually the plugin slug index
        $pluginSlug = $request->post('plugin');
        
        $installed = $this->getInstalledPlugins();
        
        if (isset($installed[$pluginSlug])) {
            $installed[$pluginSlug]['enabled'] = !$installed[$pluginSlug]['enabled'];
            $this->saveInstalledPlugins($installed);
            
            $status = $installed[$pluginSlug]['enabled'] ? 'enabled' : 'disabled';
            Session::setFlash('success', "Plugin {$status}");
        }

        Response::redirect('/admin/plugins');
    }

    /**
     * Uninstall a plugin
     */
    public function uninstall(Request $request, int $id): void
    {
        $pluginSlug = $request->post('plugin');
        
        $installed = $this->getInstalledPlugins();
        
        if (isset($installed[$pluginSlug])) {
            // Run uninstall hook if exists
            $uninstallFile = $this->pluginsDir . '/' . $pluginSlug . '/uninstall.php';
            if (file_exists($uninstallFile)) {
                require_once $uninstallFile;
            }

            unset($installed[$pluginSlug]);
            $this->saveInstalledPlugins($installed);
            
            Session::setFlash('success', 'Plugin uninstalled');
        }

        Response::redirect('/admin/plugins');
    }

    /**
     * Plugin settings page
     */
    public function settings(Request $request, string $slug): void
    {
        $pluginPath = $this->pluginsDir . '/' . $slug;
        $settingsFile = $pluginPath . '/settings.php';

        if (!file_exists($settingsFile)) {
            Session::setFlash('error', 'This plugin has no settings');
            Response::redirect('/admin/plugins');
            return;
        }

        $manifest = $this->getPluginManifest($slug);

        render('admin/plugins/settings', [
            'plugin' => $manifest,
            'slug' => $slug,
            'settingsFile' => $settingsFile,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    /**
     * Save plugin settings (POST)
     */
    public function saveSettings(Request $request, string $slug): void
    {
        csrf_validate_or_403();

        $pluginPath = $this->pluginsDir . '/' . $slug;

        // Let plugin handle its own saving if it has a save handler
        $saveHandler = $pluginPath . '/save-settings.php';
        if (file_exists($saveHandler)) {
            $_POST_DATA = $request->all();
            require_once $saveHandler;
            Session::setFlash('success', 'Settings saved');
            Response::redirect("/admin/plugins/{$slug}/settings");
            return;
        }

        // Generic: save all POST data (except _token) to plugin_settings in DB
        $pdo = Database::connection();
        $data = $request->all();
        unset($data['_token']);

        $pdo->exec("CREATE TABLE IF NOT EXISTS plugin_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            plugin_slug VARCHAR(100) NOT NULL,
            setting_key VARCHAR(191) NOT NULL,
            setting_value TEXT,
            UNIQUE KEY uk_plugin_key (plugin_slug, setting_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $stmt = $pdo->prepare("INSERT INTO plugin_settings (plugin_slug, setting_key, setting_value) 
            VALUES (:slug, :key, :val) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

        foreach ($data as $key => $value) {
            $stmt->execute([
                'slug' => $slug,
                'key' => $key,
                'val' => is_array($value) ? json_encode($value) : (string)$value
            ]);
        }

        Session::setFlash('success', 'Settings saved');
        Response::redirect("/admin/plugins/{$slug}/settings");
    }

    // ─── Private helpers ───

    private function getInstalledPlugins(): array
    {
        if (!file_exists($this->configFile)) {
            return [];
        }
        return json_decode(file_get_contents($this->configFile), true) ?: [];
    }

    private function saveInstalledPlugins(array $plugins): void
    {
        $dir = dirname($this->configFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($this->configFile, json_encode($plugins, JSON_PRETTY_PRINT));
    }

    private function scanAvailablePlugins(): array
    {
        $plugins = [];
        
        if (!is_dir($this->pluginsDir)) {
            return $plugins;
        }

        foreach (scandir($this->pluginsDir) as $dir) {
            if ($dir === '.' || $dir === '..') continue;
            
            $pluginPath = $this->pluginsDir . '/' . $dir;
            if (!is_dir($pluginPath)) continue;

            $manifest = $this->getPluginManifest($dir);
            if ($manifest) {
                $plugins[$dir] = $manifest;
            }
        }

        return $plugins;
    }

    private function getPluginManifest(string $slug): ?array
    {
        $manifestFile = $this->pluginsDir . '/' . $slug . '/plugin.json';
        
        if (!file_exists($manifestFile)) {
            return null;
        }

        $manifest = json_decode(file_get_contents($manifestFile), true);
        $manifest['slug'] = $slug;
        
        return $manifest;
    }
}
