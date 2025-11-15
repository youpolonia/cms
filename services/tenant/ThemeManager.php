<?php
declare(strict_types=1);

class ThemeManager {
    private static array $themeCache = [];
    private static string $defaultTheme = 'default';

    public static function getActiveTheme(string $tenantId): array {
        if (isset(self::$themeCache[$tenantId])) {
            return self::$themeCache[$tenantId];
        }

        $themeConfig = self::loadThemeConfig($tenantId);
        self::$themeCache[$tenantId] = self::resolveThemeInheritance($themeConfig);
        
        return self::$themeCache[$tenantId];
    }

    private static function loadThemeConfig(string $tenantId): array {
        $configPath = "themes/{$tenantId}/theme.json";
        if (!file_exists($configPath)) {
            return ['parent' => self::$defaultTheme];
        }
        
        $config = json_decode(file_get_contents($configPath), true);
        return is_array($config) ? $config : [];
    }

    private static function resolveThemeInheritance(array $config): array {
        $merged = $config;
        if (isset($config['parent']) && $config['parent'] !== self::$defaultTheme) {
            $parentConfig = self::loadThemeConfig($config['parent']);
            $merged = array_merge($parentConfig, $config);
        }
        return $merged;
    }

    public static function clearCache(?string $tenantId = null): void {
        if ($tenantId) {
            unset(self::$themeCache[$tenantId]);
        } else {
            self::$themeCache = [];
        }
    }
}
