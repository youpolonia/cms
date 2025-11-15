<?php
// Sample Plugin Helper Functions
defined('CMS_ROOT') or die('Direct access denied');

function sample_plugin_get_version() {
    return '1.0.0';
}

function sample_plugin_log_message($message) {
    if (function_exists('log_to_cms')) {
        log_to_cms('SamplePlugin: ' . $message);
    }
}
