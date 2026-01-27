<?php
require_once __DIR__ . '/../core/accesschecker.php';
require_once __DIR__ . '/../core/logger.php';
require_once __DIR__ . '/../core/pluginmanager.php';
require_once __DIR__ . '/../core/csrf.php';

csrf_boot();


require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');

// RBAC: Require admin access
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
// Check admin permissions
if (!AccessChecker::hasPermission('plugins.manage')) {
    die('Access denied');
}

$pluginSlug = $_POST['plugin'] ?? '';
if (empty($pluginSlug)) {
    die('No plugin specified');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { csrf_validate_or_403(); }

try {
    $result = PluginManager::uninstallPlugin($pluginSlug);
    Logger::log("Plugin uninstalled: $pluginSlug");
    header('Location: plugins-marketplace.php?message=uninstall_success');
} catch (\Throwable $e) {
    Logger::log("Plugin uninstall failed: " . $e->getMessage());
    http_response_code(500);
    error_log($e->getMessage());
    exit;
}
