<?php

require_once __DIR__ . '/../../../config.php';

/**
 * Admin Settings Controller
 * Handles system configuration
 */
class SettingsController {
    private $db;
    private $auth;
    private $logger;
    private $configFile;

    public function __construct($db, $auth, $logger) {
        $this->db = $db;
        $this->auth = $auth;
        $this->logger = $logger;
        $this->configFile = __DIR__ . '/../../config/settings.php';
    }

    /**
     * Show settings form
     */
    public function index() {
        if (!$this->auth->hasPermission('settings.view')) {
            return $this->forbidden();
        }

        $settings = $this->loadSettings();
        $this->render('admin/settings/index', ['settings' => $settings]);
    }

    /**
     * Update settings
     */
    public function update() {
        if (!$this->auth->hasPermission('settings.edit')) {
            return $this->forbidden();
        }

        $currentSettings = $this->loadSettings();
        $newSettings = $this->validate($_POST, [
            'site_name' => 'required|string',
            'site_email' => 'required|email',
            'timezone' => 'required|timezone',
            'maintenance_mode' => 'boolean',
            'registration_enabled' => 'boolean'
        ]);

        // Merge with existing settings to preserve any non-form values
        $settings = array_merge($currentSettings, $newSettings);
        $this->saveSettings($settings);

        $this->logger->log('Settings updated', ['changed' => array_keys($newSettings)]);
        $this->redirect('/admin/settings');
    }

    /**
     * Load settings from config file
     */
    private function loadSettings() {
        if (file_exists($this->configFile)) {
            return require_once $this->configFile;
        }
        return [];
    }

    /**
     * Save settings to config file
     */
    private function saveSettings($settings) {
        $content = "<?php\nreturn " . var_export($settings, true) . ";\n";
        file_put_contents($this->configFile, $content);
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

    private function validate($data, $rules) {
        $validated = [];
        foreach ($rules as $field => $rule) {
            $rules = explode('|', $rule);
            
            foreach ($rules as $r) {
                if ($r === 'required' && empty($data[$field])) {
                    throw new Exception("$field is required");
                }
                
                if ($r === 'email' && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("$field must be a valid email");
                }
                
                if ($r === 'timezone' && !in_array($data[$field], timezone_identifiers_list())) {
                    throw new Exception("$field must be a valid timezone");
                }
                
                if ($r === 'boolean') {
                    $data[$field] = isset($data[$field]) ? true : false;
                }
            }
            
            $validated[$field] = $data[$field];
        }
        return $validated;
    }
}
