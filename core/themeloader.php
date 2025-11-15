<?php
/**
 * Theme Loader - Handles loading and validating theme JSON files
 */
class ThemeLoader {
    /**
     * Load theme from JSON file
     * @param string $path Path to theme.json
     * @return array Theme configuration
     * @throws Exception If invalid file or structure
     */
    public static function loadFromFile(string $path): array {
        if (!file_exists($path)) {
            throw new Exception("Theme file not found: $path");
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON in theme file: " . json_last_error_msg());
        }

        if (!self::validateStructure($data)) {
            throw new Exception("Invalid theme structure");
        }

        return $data;
    }

    /**
     * Validate theme structure
     * @param array $data Theme data
     * @return bool True if valid
     */
    public static function validateStructure(array $data): bool {
        $required = ['meta', 'fonts', 'colors'];
        foreach ($required as $section) {
            if (!isset($data[$section])) {
                return false;
            }
        }

        // Validate meta section
        if (!isset($data['meta']['name']) || empty($data['meta']['name'])) {
            return false;
        }

        return true;
    }

    /**
     * Register theme from file
     * @param string $path Path to theme.json
     * @param bool $isDefault Mark as default theme
     */
    public static function registerFromFile(string $path, bool $isDefault = false): void {
        $theme = self::loadFromFile($path);
        ThemeRegistry::register($theme['meta']['name'], $theme, $isDefault);
    }
}
