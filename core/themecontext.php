<?php
/**
 * Theme Context - Handles scoped theme overrides
 */
class ThemeContext {
    private static $contexts = [];
    private static $currentContext = null;

    /**
     * Create new theme context
     * @param string $name Context identifier
     * @param array $overrides Theme overrides
     */
    public static function create(string $name, array $overrides): void {
        self::$contexts[$name] = $overrides;
    }

    /**
     * Enter a theme context
     * @param string $name Context identifier
     */
    public static function enter(string $name): void {
        if (isset(self::$contexts[$name])) {
            self::$currentContext = $name;
        }
    }

    /**
     * Exit current theme context
     */
    public static function exit(): void {
        self::$currentContext = null;
    }

    /**
     * Get theme with context overrides applied
     * @param string|null $themeName Base theme name
     * @return array Merged theme configuration
     */
    public static function getTheme(?string $themeName = null): array {
        $theme = ThemeRegistry::get($themeName);
        
        if (self::$currentContext && isset(self::$contexts[self::$currentContext])) {
            return array_merge($theme, self::$contexts[self::$currentContext]);
        }

        return $theme;
    }

    /**
     * Check if in any theme context
     */
    public static function inContext(): bool {
        return self::$currentContext !== null;
    }
}
