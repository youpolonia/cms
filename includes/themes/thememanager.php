<?php
/**
 * Theme Management System
 */
class ThemeManager {
    const EXCLUDED_DIRS = ['.', '..', 'core', 'presets'];
    const DEFAULT_THEME = 'default_public';

    /**
     * Get available public themes
     * @param string $themesDir Path to themes directory
     * @return array List of valid theme directories
     */
    public static function getAvailableThemes(string $themesDir): array {
        return array_filter(scandir($themesDir), function($item) use ($themesDir) {
            return is_dir($themesDir.'/'.$item) && 
                   !in_array($item, self::EXCLUDED_DIRS);
        });
    }

    /**
     * Validate theme exists
     * @param string $theme Theme name to validate
     * @param array $availableThemes List of valid themes
     * @return bool True if valid
     */
    public static function isValidTheme(string $theme, array $availableThemes): bool {
        return in_array($theme, $availableThemes);
    }

    /**
     * Get current active theme
     * @return string Active theme name
     */
    public static function getActiveTheme(): string {
        return get_site_setting('public_theme', self::DEFAULT_THEME);
    }

    /**
     * Update active theme
     * @param string $theme New theme name
     * @return bool True if successful
     * @throws InvalidArgumentException If theme is invalid
     */
    public static function setActiveTheme(string $theme): bool {
        $available = self::getAvailableThemes(__DIR__.'/../../../themes');
        if (!self::isValidTheme($theme, $available)) {
            throw new InvalidArgumentException("Invalid theme: $theme");
        }
        return update_site_setting('public_theme', $theme);
    }
}
