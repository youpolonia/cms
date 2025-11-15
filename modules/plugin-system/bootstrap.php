<?php
/**
 * Plugin System Bootstrap
 */

// Ensure no direct access
defined('CMS_ROOT') or die('Direct access denied');

// Initialize components
$hookSystem = new HookSystem();
$pluginManager = new PluginManager($hookSystem);
$pluginAdmin = new PluginAdmin($pluginManager);

// Register core hooks
$hookSystem->addAction('init', function() use ($pluginManager) {
    $pluginManager->loadPlugins();
});

// Register admin interface if in admin area
if (defined('CMS_ADMIN')) {
    $hookSystem->addAction('admin_menu', function() use ($pluginAdmin) {
        add_admin_page('Plugins', [$pluginAdmin, 'renderPluginPage']);
    });

    $hookSystem->addAction('admin_ajax', function($request) use ($pluginAdmin) {
        if ($request['action'] === 'plugin_management') {
            return $pluginAdmin->handleAjaxRequest($request);
        }
    });
}

// Set default sandbox mode (can be disabled per plugin)
$hookSystem->setSandboxMode(true);
