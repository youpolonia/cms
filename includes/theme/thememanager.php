<?php
/**
 * Theme Manager for CMS
 * Handles theme registration, activation, and inheritance
 */
class ThemeManager {
    private static $activeTheme = '';
    private static $registeredThemes = [];
    private static $themePaths = [];

    /**
     * Register a theme
     * @param string $themeName Unique theme identifier
     * @param string $themePath Path to theme directory
     * @param array $metadata Theme metadata (name, description, version, parent)
     */
    public static function registerTheme(string $themeName, string $themePath, array $metadata = []): void {
        if (!file_exists($themePath)) {
            throw new InvalidArgumentException("Theme directory does not exist: $themePath");
        }

        self::$registeredThemes[$themeName] = $metadata;
        self::$themePaths[$themeName] = rtrim($themePath, '/') . '/';
    }

    /**
     * Activate a theme
     * @param string $themeName Theme identifier to activate
     */
    public static function activateTheme(string $themeName): void {
        if (!isset(self::$registeredThemes[$themeName])) {
            throw new RuntimeException("Theme not registered: $themeName");
        }
        self::$activeTheme = $themeName;
    }

    /**
     * Get active theme name
     * @return string
     */
    public static function getActiveTheme(): string {
        return self::$activeTheme;
    }

    /**
     * Get active theme path
     * @return string
     */
    public static function getActiveThemePath(): string {
        return self::$themePaths[self::$activeTheme] ?? '';
    }

    /**
     * Get parent theme name if exists
     * @param string $themeName
     * @return string|null
     */
    public static function getParentTheme(string $themeName): ?string {
        return self::$registeredThemes[$themeName]['parent'] ?? null;
    }

    /**
     * Get all registered themes
     * @return array
     */
    public static function getRegisteredThemes(): array {
        return self::$registeredThemes;
    }
}
