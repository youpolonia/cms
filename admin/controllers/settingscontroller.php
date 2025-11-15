<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

namespace Admin\Controllers;

use Admin\Core\PermissionManager;
use Admin\Helpers\CSRFProtection;
use Admin\Models\SystemSettings;
use Admin\Views\View;

class SettingsController
{
    private $permissionManager;
    private $csrfProtection;
    private $settingsModel;
    private $view;

    public function __construct()
    {
        $this->permissionManager = new PermissionManager();
        $this->csrfProtection = new CSRFProtection();
        $this->settingsModel = new SystemSettings();
        $this->view = new View('admin/views/settings/');
    }

    public function index()
    {
        $this->permissionManager->verifyAdminAccess();
        
        $settings = $this->settingsModel->getAll();
        $token = $this->csrfProtection->generateToken();
        
        return $this->view->render('index.php', [
            'settings' => $settings,
            'csrfToken' => $token
        ]);
    }

    public function save()
    {
        $this->permissionManager->verifyAdminAccess();
        
        if (!$this->csrfProtection->validateToken($_POST['csrf_token'])) {
            throw new \Exception('Invalid CSRF token');
        }

        $validated = $this->validateSettings($_POST);
        $this->settingsModel->updateAll($validated);

        header('Location: /admin/settings?success=1');
        exit;
    }

    private function validateSettings(array $data): array
    {
        $validated = [];
        foreach ($data as $key => $value) {
            if (strpos($key, 'setting_') === 0) {
                $validated[$key] = htmlspecialchars($value, ENT_QUOTES);
            }
        }
        return $validated;
    }
}
