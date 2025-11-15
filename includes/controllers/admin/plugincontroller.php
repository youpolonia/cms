<?php

require_once __DIR__ . '/../../../config.php';

/**
 * Admin Plugin Controller
 * Handles plugin management operations
 */
class PluginController {
    private $db;
    private $auth;
    private $logger;
    private $pluginsDir;

    public function __construct($db, $auth, $logger) {
        $this->db = $db;
        $this->auth = $auth;
        $this->logger = $logger;
        $this->pluginsDir = __DIR__ . '/../../plugins/';
    }

    /**
     * List all plugins
     */
    public function index() {
        if (!$this->auth->hasPermission('plugins.view')) {
            return $this->forbidden();
        }

        $plugins = $this->getPlugins();
        $this->render('admin/plugins/index', ['plugins' => $plugins]);
    }

    /**
     * Activate a plugin
     */
    public function activate($pluginName) {
        if (!$this->auth->hasPermission('plugins.edit')) {
            return $this->forbidden();
        }

        $plugin = $this->validatePlugin($pluginName);
        $this->db->update('plugins', ['active' => 1], ['name' => $pluginName]);
        $this->logger->log('Plugin activated', ['plugin' => $pluginName]);
        $this->redirect('/admin/plugins');
    }

    /**
     * Deactivate a plugin
     */
    public function deactivate($pluginName) {
        if (!$this->auth->hasPermission('plugins.edit')) {
            return $this->forbidden();
        }

        $plugin = $this->validatePlugin($pluginName);
        $this->db->update('plugins', ['active' => 0], ['name' => $pluginName]);
        $this->logger->log('Plugin deactivated', ['plugin' => $pluginName]);
        $this->redirect('/admin/plugins');
    }

    /**
     * Install a plugin
     */
    public function install() {
        if (!$this->auth->hasPermission('plugins.install')) {
            return $this->forbidden();
        }

        if (!isset($_FILES['plugin_zip'])) {
            throw new Exception('No plugin file uploaded');
        }

        $file = $_FILES['plugin_zip'];
        $pluginName = pathinfo($file['name'], PATHINFO_FILENAME);
        $targetDir = $this->pluginsDir . $pluginName;

        // Validate and extract plugin
        $this->extractPlugin($file['tmp_name'], $targetDir);

        // Register plugin in database
        $pluginData = $this->readPluginInfo($pluginName);
        $this->db->insert('plugins', [
            'name' => $pluginName,
            'version' => $pluginData['version'],
            'active' => 0
        ]);

        $this->logger->log('Plugin installed', ['plugin' => $pluginName]);
        $this->redirect('/admin/plugins');
    }

    /**
     * Uninstall a plugin
     */
    public function uninstall($pluginName) {
        if (!$this->auth->hasPermission('plugins.install')) {
            return $this->forbidden();
        }

        $plugin = $this->validatePlugin($pluginName);
        
        // Delete plugin files
        $this->deleteDirectory($this->pluginsDir . $pluginName);
        
        // Remove from database
        $this->db->delete('plugins', ['name' => $pluginName]);
        
        $this->logger->log('Plugin uninstalled', ['plugin' => $pluginName]);
        $this->redirect('/admin/plugins');
    }

    private function getPlugins() {
        $installedPlugins = $this->db->query("SELECT * FROM plugins");
        $plugins = [];

        foreach ($installedPlugins as $plugin) {
            $plugin['info'] = $this->readPluginInfo($plugin['name']);
            $plugins[] = $plugin;
        }

        return $plugins;
    }

    private function validatePlugin($pluginName) {
        $plugin = $this->db->queryFirst("SELECT * FROM plugins WHERE name = ?", [$pluginName]);
        if (!$plugin) {
            throw new Exception("Plugin not found");
        }
        return $plugin;
    }

    private function readPluginInfo($pluginName) {
        $pluginFile = $this->pluginsDir . $pluginName . '/bootstrap.php';
        if (!file_exists($pluginFile)) {
            throw new Exception("Invalid plugin structure");
        }

        // Extract plugin metadata from file
        $contents = file_get_contents($pluginFile);
        preg_match('/Plugin Name:\s*(.+)/', $contents, $name);
        preg_match('/Version:\s*(.+)/', $contents, $version);
        preg_match('/Description:\s*(.+)/', $contents, $description);

        return [
            'name' => $name[1] ?? $pluginName,
            'version' => $version[1] ?? '1.0.0',
            'description' => $description[1] ?? ''
        ];
    }

    private function extractPlugin($zipFile, $targetDir) {
        // Verify ZIP file integrity
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $zipFile);
        finfo_close($finfo);
        
        if ($mime !== 'application/zip') {
            throw new Exception("Invalid file type - must be ZIP archive");
        }

        $zip = new ZipArchive;
        if ($zip->open($zipFile) !== TRUE) {
            throw new Exception("Cannot open plugin file");
        }

        // Validate ZIP contents before extraction
        $allowedExtensions = ['php', 'js', 'css', 'html', 'json', 'md'];
        $requiredFiles = ['bootstrap.php', 'plugin.json'];
        
        $hasRequiredFiles = false;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            
            // Check for path traversal
            if (strpos($filename, '../') !== false || strpos($filename, '..\\') !== false) {
                $zip->close();
                throw new Exception("Invalid path in ZIP file");
            }
            
            // Check file extensions
            if (!empty($extension) && !in_array(strtolower($extension), $allowedExtensions)) {
                $zip->close();
                throw new Exception("Disallowed file type: .$extension");
            }
            
            // Check for required files
            if (in_array(basename($filename), $requiredFiles)) {
                $hasRequiredFiles = true;
            }
        }
        
        if (!$hasRequiredFiles) {
            $zip->close();
            throw new Exception("Plugin missing required files");
        }

        // Create target directory with secure permissions
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Extract files
        $zip->extractTo($targetDir);
        $zip->close();

        // Log extraction
        $this->logger->log('Plugin extracted', [
            'file' => basename($zipFile),
            'target' => $targetDir,
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);
    }

    private function deleteDirectory($dir) {
        if (!file_exists($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    private function forbidden() {
        http_response_code(403);
        $this->render('errors/403');
        exit;
    }

    private function render($view, $data = []) {
        extract($data);
        // Secure view include: resolve under __DIR__/views and load only .php files
        $__base = realpath(__DIR__ . '/views');
        $__target = is_string($view) ? realpath($__base . DIRECTORY_SEPARATOR . $view . '.php') : false;
        if (!$__base || !$__target || strpos($__target, $__base . DIRECTORY_SEPARATOR) !== 0) {
            http_response_code(400);
            echo 'Invalid view.';
        } else {
            require_once $__target;
        }
    }

    private function redirect($url) {
        header("Location: $url");
        exit;
    }
}
