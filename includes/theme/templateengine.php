<?php
/**
 * Template Engine for CMS Themes
 * Handles template rendering with theme inheritance support
 */
class TemplateEngine {
    /**
     * Render a template file
     * @param string $templateName Template filename (without extension)
     * @param array $data Data to pass to template
     * @return string Rendered template content
     */
    public static function render(string $templateName, array $data = []): string {
        $activeTheme = \includes\ThemeManager::getActiveTheme();
        $themePath = \includes\ThemeManager::getActiveThemePath();
        $parentTheme = \includes\ThemeManager::getParentTheme($activeTheme);

        // Look for template in active theme first
        $templateFile = self::locateTemplate($templateName, $themePath);

        // If not found and has parent theme, check parent
        if (!$templateFile && $parentTheme) {
            $parentPath = \includes\ThemeManager::getThemePath($parentTheme);
            $templateFile = self::locateTemplate($templateName, $parentPath);
        }

        if (!$templateFile) {
            throw new RuntimeException("Template not found: $templateName");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        $__base = realpath(defined('CMS_ROOT') ? CMS_ROOT . '/themes' : __DIR__);
        $__target = realpath($templateFile);
        if ($__base === false || $__target === false || !str_starts_with($__target, $__base . DIRECTORY_SEPARATOR) || !is_file($__target)) {
            http_response_code(400);
            error_log('Blocked invalid theme include: ' . ($templateFile ?? 'unknown'));
            return '';
        }
        require_once $__target;
        return ob_get_clean();
    }

    /**
     * Include a template part
     * @param string $partName Template part name
     * @param array $data Data to pass to template part
     */
    public static function part(string $partName, array $data = []): void {
        echo self::render($partName, $data);
    }

    /**
     * Locate template file in theme directory
     * @param string $templateName
     * @param string $themePath
     * @return string|null Full path to template file if found
     */
    private static function locateTemplate(string $templateName, string $themePath): ?string {
        $possibleFiles = [
            $themePath . 'templates/' . $templateName . '.php',
            $themePath . $templateName . '.php'
        ];

        foreach ($possibleFiles as $file) {
            if (file_exists($file)) {
                return $file;
            }
        }

        return null;
    }

    /**
     * Get template content without rendering
     * @param string $templateName
     * @return string
     */
    public static function getTemplateContent(string $templateName): string {
        return file_get_contents(self::locateTemplate($templateName) ?? '');
    }
}
