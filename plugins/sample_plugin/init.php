<?php
// Sample Plugin Initialization
defined('CMS_ROOT') or die('Direct access denied');

function sample_plugin_init() {
    if (plugin_is_tenant_aware() && !current_tenant_supported()) {
        return;
    }
    
    // Register plugin hooks
    add_action('init', 'sample_plugin_on_init');
    add_filter('content_before_render', 'sample_plugin_content_filter');
}

function sample_plugin_on_init() {
    echo "<!-- SamplePlugin initialized -->";
}

function sample_plugin_content_filter($content) {
    return str_replace('Sample', 'Example', $content);
}
