<?php
require_once __DIR__ . '/../../config.php';

/**
 * PluginService - Core plugin management service
 */
use admin\core\services\PluginMarketplaceService;
require_once __DIR__ . '/../../core/csrf.php';
csrf_boot('admin');

class PluginService {
    use SettingsTrait;
    private static $instance;
    private $db;
    private $pluginsDir;
    private $sandboxDir;
    private $marketplace;
    private $versionComparator;

    /**
     * Get singleton instance
     */
    public static function getInstance(): self {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get all available plugins from marketplace
     */
    public function getAvailablePlugins(): array {
        return $this->marketplace->getAvailablePlugins();
    }

    /**
     * Check for plugin updates
     */
    public function checkForUpdates(): array {
        $updates = [];
        $installed = $this->getInstalledPlugins();
        $available = $this->marketplace->getAvailablePlugins();

        foreach ($installed as $plugin) {
            if (isset($available[$plugin['id']])) {
                $latest = $available[$plugin['id']];
                if ($this->versionComparator->compare($plugin['version'], $latest['version']) < 0) {
                    $updates[$plugin['id']] = [
                        'current' => $plugin['version'],
                        'available' => $latest['version'],
                        'changelog' => $latest['changelog'] ?? ''
                    ];
                }
            }
        }

        return $updates;
    }

    /**
     * Update a plugin
     */
    public function updatePlugin(string $pluginId): array {
        $result = ['success' => false, 'message' => ''];
        
        try {
            $plugin = $this->getPluginInfo($pluginId);
            $latest = $this->marketplace->getPluginDetails($pluginId);
            
            // Check if update needed
            if ($this->versionComparator->compare($plugin['version'], $latest['version']) >= 0) {
                throw new Exception("Plugin is already up to date");
            }

            // Check dependencies
            if (isset($latest['dependencies'])) {
                foreach ($latest['dependencies'] as $depId => $depVersion) {
                    $installedDep = $this->getPluginInfo($depId);
                    if (!$installedDep || $this->versionComparator->compare($installedDep['version'], $depVersion) < 0) {
                        throw new Exception("Missing or outdated dependency: $depId ($depVersion required)");
                    }
                }
            }

            // Download and install new version
            return $this->installPlugin($pluginId);
            
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Get plugin dependencies
     */
    public function getPluginDependencies(string $pluginId): array {
        $plugin = $this->getPluginInfo($pluginId);
        return $plugin['dependencies'] ?? [];
    }

    private function __construct() {
        require_once __DIR__ . '/../../core/database.php';
        $this->db = \core\Database::connection();
        $this->pluginsDir = __DIR__ . '/../../plugins/';
        $this->sandboxDir = __DIR__ . '/../../plugins/sandbox/';
        $this->marketplace = new PluginMarketplaceService();
        $this->versionComparator = new SemanticVersionComparator();
        
        // Ensure directories exist
        if (!file_exists($this->pluginsDir)) {
            mkdir($this->pluginsDir, 0755, true);
        }
        if (!file_exists($this->sandboxDir)) {
            mkdir($this->sandboxDir, 0755, true);
        }
    }


    /**
     * Get installed plugins
     */
    public function getInstalledPlugins(): array {
        $stmt = $this->db->prepare("
            SELECT p.*, ps.settings
            FROM plugins p
            LEFT JOIN plugin_settings ps ON p.id = ps.plugin_id
        ");
        $stmt->execute();
        $plugins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map(function($plugin) {
            if ($plugin['settings']) {
                $plugin['settings'] = json_decode($plugin['settings'], true);
            }
            return $plugin;
        }, $plugins);
    }

    /**
     * Install a plugin with validation
     */
    public function installPlugin(string $pluginId, ?string $licenseKey = null): array {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_validate_or_403(); }
        $result = ['success' => false, 'message' => ''];
        
        try {
            // Get plugin info from registry
            $plugin = $this->getPluginInfo($pluginId);
            if (!$plugin) {
                throw new Exception("Plugin not found in registry");
            }

            // Validate plugin
            if (!$this->validatePlugin($plugin)) {
                throw new Exception("Plugin not compatible with your system");
            }

            // Validate license if required
            if ($plugin['license_type'] !== 'free' && empty($licenseKey)) {
                throw new Exception("License key required for this plugin");
            }

            // Download plugin package
            $tempFile = $this->downloadPlugin($plugin['download_url']);
            if (!$tempFile) {
                throw new Exception("Failed to download plugin");
            }

            // Verify package signature
            if (!$this->verifyPackage($tempFile, $plugin['signature'])) {
                unlink($tempFile);
                throw new Exception("Plugin package verification failed");
            }

            // Extract to sandbox first
            $sandboxPath = $this->sandboxDir . $pluginId . '/';
            if (!$this->extractPackage($tempFile, $sandboxPath)) {
                throw new Exception("Failed to extract plugin package");
            }

            // Run in sandbox for validation
            if (!$this->runInSandbox($sandboxPath)) {
                throw new Exception("Plugin failed sandbox validation");
            }

            // Move to plugins directory
            $pluginPath = $this->pluginsDir . $pluginId . '/';
            if (!rename($sandboxPath, $pluginPath)) {
                throw new Exception("Failed to install plugin");
            }

            // Register in database
            $this->registerPlugin($pluginId, $plugin);

            $result['success'] = true;
            $result['message'] = "Plugin installed successfully";

        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
            // Clean up any partial installation
            $this->cleanupFailedInstall($pluginId);
        }

        return $result;
    }

    /**
     * Validate plugin compatibility
     */
    private function validatePlugin(array $plugin): bool {
        // Check PHP version
        if (isset($plugin['requires_php']) && 
            version_compare(PHP_VERSION, $plugin['requires_php'], '<')) {
            return false;
        }

        // Check CMS version
        if (isset($plugin['requires_cms']) && 
            version_compare(CMS_VERSION, $plugin['requires_cms'], '<')) {
            return false;
        }

        return true;
    }

    /**
     * Run plugin in sandbox environment
     */
    private function runInSandbox(string $pluginPath): bool {
        // Isolate execution
        $result = false;
        $sandbox = new SandboxEnvironment();
        
        try {
            $result = $sandbox->execute(function() use ($pluginPath) {
                // Load plugin bootstrap file
                $bootstrap = $pluginPath . 'bootstrap.php';
                if (!file_exists($bootstrap)) {
                    throw new Exception("Missing bootstrap.php");
                }
                
                // Validate file structure
                $this->validatePluginStructure($pluginPath);
                
                // Run in isolated scope
                $plugin = require_once $bootstrap;
                
                // Verify plugin interface and metadata
                if (!$plugin instanceof PluginInterface) {
                    throw new Exception("Invalid plugin interface");
                }
                
                // Check required methods
                $this->validatePluginMethods($plugin);
                
                return true;
            });
        } catch (Exception $e) {
            error_log("Sandbox execution failed: " . $e->getMessage());
        }
        
        return $result;
    }
    
    /**
     * Validate plugin directory structure
     */
    private function validatePluginStructure(string $pluginPath): void {
        $requiredFiles = [
            'bootstrap.php',
            'plugin.json',
            'src/',
            'assets/'
        ];
        
        foreach ($requiredFiles as $file) {
            if (!file_exists($pluginPath . $file)) {
                throw new Exception("Missing required file/directory: $file");
            }
        }
    }
    
    /**
     * Validate plugin implements required methods
     */
    private function validatePluginMethods(PluginInterface $plugin): void {
        $requiredMethods = [
            'getName',
            'getVersion',
            'init',
            'activate',
            'deactivate'
        ];
        
        foreach ($requiredMethods as $method) {
            if (!method_exists($plugin, $method)) {
                throw new Exception("Missing required method: $method");
            }
        }
    }

    /**
     * Get plugin info from registry
     */
    private function getPluginInfo(string $pluginId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM plugin_registry WHERE id = ?");
        $stmt->execute([$pluginId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Download plugin package
     */
    private function downloadPlugin(string $url): ?string {
        require_once __DIR__ . '/../../core/tmp_sandbox.php';
        $tempFile = tempnam(cms_tmp_dir(), 'plugin_');
        $fp = fopen($tempFile, 'w+');
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        if (!curl_exec($ch)) {
            fclose($fp);
            unlink($tempFile);
            return null;
        }
        
        curl_close($ch);
        fclose($fp);
        return $tempFile;
    }

    /**
     * Verify package signature
     */
    private function verifyPackage(string $filePath, string $signature): bool {
        $fileHash = hash_file('sha256', $filePath);
        return hash_equals($signature, $fileHash);
    }

    /**
     * Extract plugin package
     */
    private function extractPackage(string $filePath, string $targetDir): bool {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return false;
        }
        
        if (!$zip->extractTo($targetDir)) {
            $zip->close();
            return false;
        }
        
        return $zip->close();
    }

    /**
     * Register plugin in database
     */
    private function registerPlugin(string $pluginId, array $pluginData): bool {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_validate_or_403(); }
        $this->db->beginTransaction();
        
        try {
            // Register main plugin info
            $stmt = $this->db->prepare("
                INSERT INTO plugins
                (id, name, version, author, description, license_type, installed_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $pluginId,
                $pluginData['name'],
                $pluginData['version'],
                $pluginData['author'],
                $pluginData['description'],
                $pluginData['license_type']
            ]);
            
            // Initialize settings
            $settingsStmt = $this->db->prepare("
                INSERT INTO plugin_settings
                (plugin_id, settings)
                VALUES (?, ?)
            ");
            
            $defaultSettings = $this->getDefaultSettings($pluginId);
            $settingsStmt->execute([
                $pluginId,
                json_encode($defaultSettings)
            ]);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Plugin registration failed: " . $e->getMessage());
            return false;
        }
    }
    
    private function getDefaultSettings(string $pluginId): array {
        $pluginPath = $this->pluginsDir . $pluginId . '/';
        $configFile = $pluginPath . 'config/settings.json';
        
        if (file_exists($configFile)) {
            return json_decode(file_get_contents($configFile), true);
        }
        
        return [];
    }

    /**
     * Clean up failed installation
     */
    private function cleanupFailedInstall(string $pluginId): void {
        $sandboxPath = $this->sandboxDir . $pluginId . '/';
        $pluginPath = $this->pluginsDir . $pluginId . '/';
        
        if (file_exists($sandboxPath)) {
            $this->rrmdir($sandboxPath);
        }
        
        if (file_exists($pluginPath)) {
            $this->rrmdir($pluginPath);
        }
    }

    /**
     * Recursively remove directory
     */
    private function rrmdir(string $dir): void {
        foreach (glob($dir . '/*') as $file) {
            is_dir($file) ? $this->rrmdir($file) : unlink($file);
        }
        rmdir($dir);
    }
}
