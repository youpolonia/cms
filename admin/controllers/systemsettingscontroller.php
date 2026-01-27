<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once __DIR__ . '/../../core/csrf.php';
require_once __DIR__ . '/../includes/configmanager.php';
/**
 * System Settings Controller
 * Handles site configuration, email settings, and maintenance mode
 */
class SystemSettingsController {
    private $settingsFile = __DIR__ . '/../../config/site_settings.php';
    private $maintenanceFlag = __DIR__ . '/../../config/maintenance.flag';
    private $logModel;
    
    public function __construct(\Includes\Models\LogModel $logModel) {
        $this->logModel = $logModel;
        $this->ensureSettingsFile();
    }
    
    private function ensureSettingsFile() {
        if (!file_exists($this->settingsFile)) {
            $defaultSettings = [
                'site_title' => 'My CMS',
                'admin_email' => 'admin@example.com',
                'smtp_host' => '',
                'smtp_port' => 587,
                'smtp_username' => '',
                'smtp_password' => '',
                'smtp_secure' => 'tls'
            ];
            file_put_contents($this->settingsFile, "<?php\nreturn " . var_export($defaultSettings, true) . ";\n");
        }
    }
    
    public function index() {
        $settings = require_once $this->settingsFile;
        $settings['maintenance_mode'] = file_exists($this->maintenanceFlag);
        require_once __DIR__ . '/../views/system/settings.php';
    }
    
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_validate_or_403();
            require_once __DIR__ . '/../../api/middleware/tenantsecuritymiddleware.php';
            $tenantId = $_SERVER['HTTP_X_TENANT_ID'] ?? '';
            
            // Verify tenant access and prevent cross-tenant POST
            TenantSecurityMiddleware::enforceTenantHeader();
            TenantSecurityMiddleware::preventCrossTenantPost($tenantId);
            
            if (!TenantSecurityMiddleware::verifySettingsAccess($tenantId)) {
                http_response_code(403);
                die('Unauthorized tenant settings access');
            }

            $currentSettings = require_once $this->settingsFile;
            $userId = $_SESSION['user_id'] ?? 0;

            // Handle maintenance mode flag
            if (isset($_POST['maintenance_mode'])) {
                file_put_contents($this->maintenanceFlag, '');
            } else {
                if (file_exists($this->maintenanceFlag)) {
                    unlink($this->maintenanceFlag);
                }
            }

            // Prepare new settings and track changes
            $newSettings = [
                'site_title' => $_POST['site_title'] ?? '',
                'admin_email' => $_POST['admin_email'] ?? '',
                'smtp_host' => $_POST['smtp_host'] ?? '',
                'smtp_port' => (int)$_POST['smtp_port'] ?? 587,
                'smtp_username' => $_POST['smtp_username'] ?? '',
                'smtp_password' => $_POST['smtp_password'] ?? '',
                'smtp_secure' => $_POST['smtp_secure'] ?? 'tls'
            ];

            // Log each changed setting
            foreach ($newSettings as $key => $newValue) {
                $oldValue = $currentSettings[$key] ?? null;
                if ($oldValue != $newValue) {
                    $this->logModel->logSettingChange($userId, $key, $oldValue, $newValue);
                }
            }

            file_put_contents($this->settingsFile, "<?php\nreturn " . var_export($newSettings, true) . ";\n");
            header('Location: ' . ADMIN_BASE . '/system/settings?saved=1');
            exit;
        }
    }
}
