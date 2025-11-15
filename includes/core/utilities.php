<?php
require_once __DIR__ . '/../helpers/env.php';
/**
 * Core Utilities for CMS
 *
 * @package Core
 */

if (!defined('CMS_ROOT')) {
    die('Invalid access');
}

class Utilities {
    private static $envCache = [];

    /**
     * Load environment configuration with optional default value
     * 
     * @param string $key Environment variable name
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public static function loadEnvConfig(string $key, $default = null) {
        if (isset(self::$envCache[$key])) {
            return self::$envCache[$key];
        }
        // No .env parsing; use env() helper (getenv/$_ENV) with default
        $value = function_exists('env') ? env($key, $default) : $default;
        self::$envCache[$key] = $value;
        return $value;
    }
}
