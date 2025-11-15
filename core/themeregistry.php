<?php
/**
 * Theme Registry - Manages theme registration and activation
 */
class ThemeRegistry {
    private static array $themes = [];
    private static ?string $activeTheme = null;

    /**
     * Register a theme
     */
    public static function register(string $name, array $config, bool $isDefault = false): void {
        self::$themes[$name] = $config;
        
        if ($isDefault && self::$activeTheme === null) {
            self::$activeTheme = $name;
        }
    }

    /**
     * Get theme by name
     */
    public static function get(string $name): ?array {
        return self::$themes[$name] ?? null;
    }

    /**
     * Get all registered themes
     */
    public static function getAll(): array {
        return self::$themes;
    }

    /**
     * Set active theme
     */
    public static function setActive(string $name, bool $active): bool {
        if (!isset(self::$themes[$name])) {
            return false;
        }

        if ($active) {
            self::$activeTheme = $name;
        } elseif (self::$activeTheme === $name) {
            self::$activeTheme = null;
        }

        return true;
    }

    /**
     * Get active theme name
     */
    public static function getActive(): ?string {
        return self::$activeTheme;
    }

    /**
     * Get active theme config
     */
    public static function getActiveConfig(): ?array {
        return self::$activeTheme ? self::$themes[self::$activeTheme] : null;
    }
}
