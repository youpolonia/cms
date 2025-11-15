<?php
namespace Core;

class Theme {
    private static $currentTheme = 'default';
    private static $themePath = 'public/themes';
    private static $templatePath = 'templates';

    public static function setCurrent(string $themeName): void {
        self::$currentTheme = $themeName;
    }

    public static function getCurrent(): string {
        return self::$currentTheme;
    }

    public static function getPath(string $themeName = null): string {
        $theme = $themeName ?? self::$currentTheme;
        return self::$themePath . '/' . $theme;
    }

    public static function getTemplatePath(string $themeName = null): string {
        return self::getPath($themeName) . '/' . self::$templatePath;
    }

    public static function render(string $template, array $data = []): string {
        $templateFile = self::getTemplatePath() . '/' . $template . '.php';
        
        if (!is_readable($templateFile)) {
            throw new \RuntimeException("Template file not found or not readable: $templateFile");
        }

        extract($data);
        ob_start();
        require_once $templateFile;
        return ob_get_clean();
    }

    public static function asset(string $path): string {
        return '/' . self::getPath() . '/assets/' . ltrim($path, '/');
    }
}
