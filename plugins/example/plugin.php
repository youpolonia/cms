<?php
/**
 * Example Plugin Bootstrap File
 * 
 * This file is loaded by the PluginLoader to initialize the plugin
 */

// Explicitly require necessary class files
require_once __DIR__ . '/exampleplugin.php';
// Assuming PluginLoader is in includes/plugins based on CMS namespace
// and typical project structure.
// The path might need adjustment if PluginLoader.php is located elsewhere.
require_once __DIR__ . '/../../includes/plugins/PluginLoader.php';

// Get HookManager instance from PluginLoader
$hookManager = \CMS\Plugins\PluginLoader::getHookManager();

// Create and initialize plugin instance
$plugin = new \Example\ExamplePlugin($hookManager);
$plugin->init();

// Register plugin with the system
$pluginRegistry = \CMS\Plugins\PluginLoader::getRegistry();
$pluginRegistry->registerPlugin(
    'example',
    $plugin->getMetadata(),
    [] // No dependencies
);
