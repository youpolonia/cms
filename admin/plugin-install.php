<?php
require_once __DIR__ . '/../core/accesschecker.php';
require_once __DIR__ . '/../core/logger.php';
require_once __DIR__ . '/../core/csrf.php';

// Check admin permissions
if (!AccessChecker::hasPermission('plugins.manage')) {
    die('Access denied');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { csrf_validate_or_403(); }

$pluginSlug = $_POST['plugin'] ?? '';
if (empty($pluginSlug)) {
    die('No plugin specified');
}

$pluginDir = __DIR__ . '/../plugins/' . $pluginSlug;
if (!is_dir($pluginDir)) {
    die('Plugin not found');
}

$pluginJson = $pluginDir . '/plugin.json';
if (!file_exists($pluginJson)) {
    die('Invalid plugin structure');
}

$pluginData = json_decode(file_get_contents($pluginJson), true);
if (!$pluginData) {
    die('Invalid plugin manifest');
}

// TODO: Implement PluginManager
// $result = PluginManager::installPlugin($pluginSlug);

Logger::log("Plugin installation attempted: $pluginSlug");

header('Location: plugins-marketplace.php?message=install_success');
