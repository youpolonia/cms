<?php
/**
 * Email Settings Helper
 * Manages config/email_settings.json
 * No DB access, no framework dependencies
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/error_handler.php';

if (!function_exists('email_settings_get')) {
    /**
     * Get email settings from JSON file
     * @return array Settings array with defaults if file missing
     */
    function email_settings_get(): array {
        $defaults = [
            'from_name'       => '',
            'from_email'      => '',
            'reply_to_email'  => '',
            'send_mode'       => 'smtp'
        ];

        $path = CMS_ROOT . '/config/email_settings.json';

        if (!file_exists($path)) {
            return $defaults;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return $defaults;
        }

        $decoded = json_decode($content, true);
        if (!is_array($decoded)) {
            return $defaults;
        }

        // Merge with defaults to ensure all keys exist
        return array_merge($defaults, $decoded);
    }
}

if (!function_exists('email_settings_update')) {
    /**
     * Update email settings in JSON file
     * @param array $new New settings to merge
     * @return void
     */
    function email_settings_update(array $new): void {
        $path = CMS_ROOT . '/config/email_settings.json';

        // Load existing settings
        $existing = email_settings_get();

        // Merge new values
        $merged = array_merge($existing, $new);

        // Encode with pretty print
        $json = json_encode(
            $merged,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {
            error_log('Email settings: JSON encode failed');
            return;
        }

        // Ensure newline at EOF
        $json .= "\n";

        // Write to file
        $result = @file_put_contents($path, $json, LOCK_EX);

        if ($result === false) {
            error_log('Email settings: Failed to write to ' . $path);
        }
    }
}
