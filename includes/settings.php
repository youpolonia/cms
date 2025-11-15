<?php
declare(strict_types=1);

/**
 * Site settings management
 */

/**
 * Get site setting value
 * @param string $key Setting key
 * @param mixed $default Default value if setting not found
 * @return mixed Setting value
 */
function get_site_setting(string $key, $default = null) {
    static $settings = null;
    
    if ($settings === null) {
        $settings = require_once __DIR__ . '/../config/settings.php';
    }

    return $settings[$key] ?? $default;
}

/**
 * Update site setting
 * @param string $key Setting key
 * @param mixed $value New setting value
 * @return bool True on success
 */
function update_site_setting(string $key, $value): bool {
    $settingsFile = __DIR__ . '/../config/settings.php';
    $settings = require_once $settingsFile;
    
    $settings[$key] = $value;
    $content = "<?php\nreturn " . var_export($settings, true) . ";\n";
    
    return file_put_contents($settingsFile, $content) !== false;
}
