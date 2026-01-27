<?php
require_once __DIR__ . '/../core/csrf.php';
require_once __DIR__ . '/includes/security.php';
verifyAdminAccess();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { csrf_validate_or_403(); }

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token');
}

require_once __DIR__ . '/../models/settingsmodel.php';

$tenant_id = $_POST['tenant_id'] ?? null;
$settingsModel = new SettingsModel();

foreach ($_POST as $key => $value) {
    if (strpos($key, 'setting_') === 0) {
        $settingName = substr($key, 8);
        $settingsModel->saveSettings($settingName, $value, $tenant_id);
    }
}

$_SESSION['backup_message'] = 'Settings saved successfully' . 
    ($tenant_id ? " for tenant $tenant_id" : " globally");
header('Location: settings.php');
exit;
