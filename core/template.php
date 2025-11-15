<?php
/**
 * Template path resolution system for CMS
 */
class Template {
    /**
     * Base paths to search for templates
     * @var array
     */
    private static $searchPaths = [
        'templates',
        'views',
        'admin/views'
    ];

    /**
     * Resolve template path with fallback hierarchy
     * @param string $templateName Template name/path
     * @return string|null Full path if found, null otherwise
     */
    public static function resolve(string $templateName): ?string {
        // Check if already absolute path
        if (file_exists($templateName)) {
            return $templateName;
        }

        // Check relative to search paths
        foreach (self::$searchPaths as $path) {
            $fullPath = "$path/$templateName";
            if (file_exists($fullPath)) {
                return $fullPath;
            }

            // Check with .php extension if not provided
            if (!str_ends_with($templateName, '.php')) {
                $fullPath = "$fullPath.php";
                if (file_exists($fullPath)) {
                    return $fullPath;
                }
            }
        }

        // Fallback to default theme
        $themePath = "themes/default/$templateName";
        if (file_exists($themePath)) {
            return $themePath;
        }

        // Fallback to core templates
        $corePath = "core/templates/$templateName";
        if (file_exists($corePath)) {
            return $corePath;
        }

        return null;
    }

    /**
     * Include template with fallback support
     * @param string $templateName Template to require_once
     * @param array $data Data to extract
     * @throws RuntimeException If template not found
     */
    public static function render(string $templateName, array $data = []): void {
        $resolvedPath = self::resolve($templateName);
        if ($resolvedPath === null) {
            throw new RuntimeException("Template not found: $templateName");
        }

        extract($data);
        $__base = realpath(defined('CMS_ROOT') ? CMS_ROOT . '/templates' : __DIR__);
        $__target = realpath($resolvedPath);
        if ($__base === false || $__target === false || !str_starts_with($__target, $__base . DIRECTORY_SEPARATOR) || !is_file($__target)) {
            http_response_code(400);
            error_log('Blocked invalid template include: ' . ($resolvedPath ?? 'unknown'));
            return;
        }
        require_once $__target;
    }

    /**
     * Add additional search path
     * @param string $path Path to add
     */
    public static function addSearchPath(string $path): void {
        if (!in_array($path, self::$searchPaths)) {
            self::$searchPaths[] = $path;
        }
    }
}
