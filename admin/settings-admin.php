<?php
require_once __DIR__ . '/../includes/ai/AIFallbackManager.php';

$lastFallback = AIFallbackManager::getLastFallback();
if ($lastFallback && time() - $lastFallback['timestamp'] < 86400) {
    echo '<div class="alert alert-warning">Warning: AI fallback was used recently ('
        . date('Y-m-d H:i', $lastFallback['timestamp'])
        . '). Check logs for details.</div>';
}
// Admin Settings Interface
require_once __DIR__ . '/../includes/core/auth.php';
require_once __DIR__ . '/../includes/core/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session

require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');

// RBAC: Require admin access
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
require_once __DIR__ . '/../includes/services/DatabaseTenantConfigStorage.php';
require_once __DIR__ . '/../core/csrf.php';

// Check admin access
if (!Auth::hasPermission('admin')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

csrf_boot('admin');

// Initialize config service
$config = new DatabaseTenantConfigStorage();
$currentSettings = $config->getAll();

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    try {
        $updatedSettings = [];
        foreach ($_POST as $key => $value) {
            if (array_key_exists($key, $currentSettings)) {
                $updatedSettings[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }
        
        if (!empty($updatedSettings)) {
            foreach ($updatedSettings as $key => $value) {
                $config->set($key, $value);
            }
            $message = 'Settings updated successfully';
            $currentSettings = array_merge($currentSettings, $updatedSettings);
        }
    } catch (Exception $e) {
        $message = 'Error updating settings: ' . $e->getMessage();
    }
}

// Render settings form
?><!DOCTYPE html>
<html>
<head>
    <title>CMS Settings</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: inline-block; width: 200px; }
        input { padding: 5px; width: 300px; }
        .message { padding: 10px; margin: 10px 0; }
        .success { background: #dff0d8; color: #3c763d; }
        .error { background: #f2dede; color: #a94442; }
    </style>
</head>
<body>
    <h1>CMS Settings</h1>
    
    <?php if ($message): ?>
        <div class="message <?= strpos($message, 'Error') !== false ? 'error' : 'success' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <?php csrf_field(); ?>
        <?php foreach ($currentSettings as $key => $value): ?>
            <div class="form-group">
                <label for="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($key) ?>:</label>
                <input type="text" name="<?= htmlspecialchars($key) ?>"
                       value="<?= htmlspecialchars($value) ?>" id="<?= htmlspecialchars($key) ?>">
            </div>
        <?php endforeach; ?>
        <div class="form-group">
            <input type="submit" value="Save Settings">
        </div>
    </form>
</body>
</html>
