<?php
require_once __DIR__ . '/../themes/core/themestoragehandler.php';

class ThemeManager {
    private static $themesPath = __DIR__ . '/../themes/';
    private static $activeTheme = 'default';

    public static function getAvailableThemes(): array {
        $themes = [];
        $globResult = glob(self::$themesPath . '*', GLOB_ONLYDIR);
        if (is_array($globResult)) {
            foreach ($globResult as $themeDir) {
                $themeId = basename($themeDir);
                if (file_exists($themeDir . '/theme.json')) {
                    $themes[] = $themeId;
                }
            }
        }
        return $themes;
    }

    public static function getActiveTheme(): string {
        try {
            $settings = SettingsModel::getSettings();
            return $settings['active_theme'] ?? self::$activeTheme;
        } catch (Exception $e) {
            error_log("ThemeManager: Failed to get settings - " . $e->getMessage());
            return self::$activeTheme;
        }
    }

    public static function renderLayout(string $template, array $data = []): string {
        $theme = self::getActiveTheme();
        $templatePath = self::$themesPath . "{$theme}/templates/{$template}.php";

        $base = realpath(self::$themesPath);
        $resolved = realpath($templatePath);
        if ($base === false || $resolved === false || !str_starts_with($resolved, $base . DIRECTORY_SEPARATOR) || !is_file($resolved)) {
            http_response_code(400);
            error_log('Blocked invalid template path: ' . $template);
            throw new Exception("Invalid template path");
        }

        extract($data);
        ob_start();
        require_once $resolved;
        return ob_get_clean();
    }

    public static function getThemeMetadata(string $themeId): array {
        $themePath = self::$themesPath . "{$themeId}/theme.json";
        if (!file_exists($themePath)) {
            return [];
        }

        $metadata = json_decode(file_get_contents($themePath), true);
        return is_array($metadata) ? $metadata : [];
    }

    public static function getThemeVersions(string $themeId): array {
        return ThemeStorageHandler::getThemeVersions($themeId);
    }
}
