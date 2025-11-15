<?php

/**
 * Enhanced environment variable helper function
 *
 * Combines features from env() and get_env_var() functions:
 * - Type conversion for boolean, numeric, and null values
 * - Multiple lookup sources (getenv(), $_ENV, .env file fallback)
 * - Backward compatibility with existing env() usage
 * - Cached .env file parsing for performance
 */

if (!function_exists('env')) {
    function env(string $key, $default = null) {
        // First check $_ENV superglobal (from web server or putenv)
        $value = $_ENV[$key] ?? null;
        
        // Then check getenv() if not found in $_ENV
        if ($value === null) {
            $value = getenv($key);
            if ($value === false) {
                $value = null;
            }
        }

        // No .env file fallback

        // Return default if not found anywhere
        if ($value === null) {
            return $default;
        }

        // Convert string values to appropriate types
        return convert_env_value($value);
    }
}

// parse_env_file removed

if (!function_exists('convert_env_value')) {
    /**
     * Convert environment variable value to appropriate type
     *
     * @param mixed $value Raw environment value
     * @return mixed Converted value (bool, int, float, string, or null)
     */
    function convert_env_value($value) {
        if (!is_string($value)) {
            return $value;
        }
        
        $lowerValue = strtolower($value);
        
        // Handle boolean values
        switch ($lowerValue) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }
        
        // Handle numeric values
        if (is_numeric($value)) {
            return strpos($value, '.') === false ? (int)$value : (float)$value;
        }
        
        return $value;
    }
}
