<?php
// Example plugin bootstrap file

/**
 * @param PluginManager $pluginManager The plugin manager instance
 */
return function($pluginManager) {
    // Register hooks directly with PluginManager
    $pluginManager->addHook('init', function() {
    // Plugin initialization code
    error_log("ExamplePlugin initialized");
});

$pluginManager->addHook('admin_init', function() {
    // Admin-specific initialization
    error_log("ExamplePlugin admin initialized");
});

$pluginManager->addFilter('content_before_render', function($content) {
    // Example content filter
    return str_replace('Example', 'Demo', $content);
});

// Plugin activation handler
register_shutdown_function(function() use ($pluginManager) {
    if (defined('PLUGIN_ACTIVATION_MODE')) {
        $pluginManager->addHook('activate_ExamplePlugin', function() {
            // Activation logic here
            file_put_contents(__DIR__ . '/activated.log', date('Y-m-d H:i:s'));
        });
    }
    });
};
