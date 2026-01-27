<?php
/**
 * General Settings Helper
 * Manages config/general_settings.json
 * No DB access, no framework dependencies
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

if (!function_exists('general_settings_get')) {
    /**
     * Get general settings from JSON file
     * @return array Settings array with defaults if file missing
     */
    function general_settings_get(): array {
        $defaults = [
            'site_name' => '',
            'contact_email' => '',
            'timezone' => 'UTC',
            'homepage_title_suffix' => '',
            'homepage_description' => ''
        ];

        $path = CMS_ROOT . '/config/general_settings.json';

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

if (!function_exists('general_settings_update')) {
    /**
     * Update general settings in JSON file
     * @param array $new New settings to merge
     * @return bool Success status
     */
    function general_settings_update(array $new): bool {
        $path = CMS_ROOT . '/config/general_settings.json';

        // Load existing settings
        $existing = general_settings_get();

        // Merge new values (preserves unknown keys)
        $merged = array_merge($existing, $new);

        // Encode with pretty print
        $json = json_encode(
            $merged,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        if ($json === false) {
            return false;
        }

        // Ensure newline at EOF
        $json .= "\n";

        // Write to file
        $result = file_put_contents($path, $json, LOCK_EX);

        return $result !== false;
    }
}
