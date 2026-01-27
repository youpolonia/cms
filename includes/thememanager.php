<?php
/**
 * ThemeManager - Handles theme loading, validation and management
 * Supports dual themes (public and admin) with context-based selection
 * Includes theme registration, inheritance and caching
 */

require_once __DIR__ . '/../core/database.php';
class ThemeManager {
    const CONTEXT_PUBLIC = 'public';
    const CONTEXT_ADMIN = 'admin';

    private static array $themeCache = [];
    private static array $registeredThemes = [];
    private static array $themePaths = [];
    private static string $defaultTheme = 'default';

    /**
     * Get current tenant ID with fallback
     * @return int Tenant ID (defaults to 1)
     */
    private static function getCurrentTenantId(): int {
        // Check if Tenant class has static currentId method
        if (class_exists('Tenant') && method_exists('Tenant', 'currentId')) {
            return Tenant::currentId();
        }

        // Check session for tenant ID
        if (isset($_SESSION['tenant_id']) && is_numeric($_SESSION['tenant_id'])) {
            return (int)$_SESSION['tenant_id'];
        }

        // Default to tenant ID 1
        return 1;
    }
    
    /**
     * Register a theme
     * @param string $themeName Unique theme identifier
     * @param string $themePath Path to theme directory
     * @param array $metadata Theme metadata (name, description, version, parent)
     * @param string $context Theme context (public/admin)
     */
    public static function registerTheme(string $themeName, string $themePath, array $metadata = [], string $context = self::CONTEXT_PUBLIC): void {
        if (!file_exists($themePath)) {
            throw new InvalidArgumentException("Theme directory does not exist: $themePath");
        }

        $key = $context . '_' . $themeName;
        self::$registeredThemes[$key] = $metadata;
        self::$themePaths[$key] = rtrim($themePath, '/') . '/';
    }

    /**
     * Register widget regions for a theme
     * @param string $themeName Theme identifier
     * @param array $regions Array of region names
     * @param string $context Theme context (public/admin)
     */
    public static function registerThemeRegions(string $themeName, array $regions, string $context = self::CONTEXT_PUBLIC): void {
        $key = $context . '_' . $themeName;
        if (!isset(self::$registeredThemes[$key])) {
            throw new RuntimeException("Theme not registered: $themeName ($context)");
        }

        self::$registeredThemes[$key]['regions'] = $regions;
    }

    /**
     * Get all registered regions for a theme
     * @param string $themeName Theme identifier
     * @param string $context Theme context (public/admin)
     * @return array List of regions
     */
    public static function getThemeRegions(string $themeName, string $context = self::CONTEXT_PUBLIC): array {
        $key = $context . '_' . $themeName;
        if (!isset(self::$registeredThemes[$key])) {
            throw new RuntimeException("Theme not registered: $themeName ($context)");
        }

        return self::$registeredThemes[$key]['regions'] ?? [];
    }

    /**
     * Render a public theme view
     * @param string $viewName Name of the view to render
     * @param array $data Data to pass to the view
     * @param string|null $themeName Optional theme name (default: active public theme)
     * @return string Rendered view content
     */
    /**
     * Get widget template path if exists
     * @param string $widgetId Widget identifier
     * @return string|null Template path or null if not found
     */
    public static function getWidgetTemplate(string $widgetId): ?string {
        $themeName = self::getActiveTheme(self::getCurrentTenantId(), self::CONTEXT_PUBLIC);
        if (!$themeName) {
            return null;
        }

        $templatePath = "themes/{$themeName}/widgets/{$widgetId}.php";
        return file_exists($templatePath) ? $templatePath : null;
    }

    /**
     * Render widgets for a specific region
     * @param string $region Region identifier
     * @param array $context Rendering context
     * @param string|null $themeName Optional theme name
     * @return string Rendered widget HTML
     */
    public static function render_region_widgets(string $region, array $context = [], ?string $themeName = null): string {
        $tenantId = self::getCurrentTenantId();
        $themeName = $themeName ?? self::getActiveTheme($tenantId, self::CONTEXT_PUBLIC);
        if (!$themeName) {
            return '';
        }

        // Validate region exists for theme
        $regions = self::getThemeRegions($themeName);
        if (!in_array($region, $regions)) {
            throw new InvalidArgumentException("Invalid region '$region' for theme '$themeName'");
        }

        return WidgetManager::renderRegionWidgets($tenantId, $region, array_merge($context, [
            'theme' => $themeName
        ]));
    }

    public static function render_theme_view_public(string $viewName, array $data = [], ?string $themeName = null): string {
        $themeName = $themeName ?? self::getActiveTheme(self::getCurrentTenantId(), self::CONTEXT_PUBLIC);
        if (!$themeName) { $themeName = 'default_public'; }
        $baseDir = dirname(__DIR__) . '/themes/' . $themeName;
        
        // Try views/ first, then templates/ for compatibility with AI-generated themes
        $viewFile = $baseDir . '/views/' . $viewName . '.php';
        if (!file_exists($viewFile)) {
            $viewFile = $baseDir . '/templates/' . $viewName . '.php';
        }
        
        if (!file_exists($viewFile)) {
            // Fallback to default_public theme
            $fallbackBase = dirname(__DIR__) . '/themes/default_public';
            $fallbackView = $fallbackBase . '/views/' . $viewName . '.php';
            if (!file_exists($fallbackView)) {
                $fallbackView = $fallbackBase . '/templates/' . $viewName . '.php';
            }
            if (!file_exists($fallbackView)) {
                http_response_code(500);
                return 'View file not found: ' . str_replace(dirname(__DIR__) . '/', '', $viewFile);
            }
            $baseDir = $fallbackBase;
            $viewFile = $fallbackView;
        }
        if (!empty($data)) { extract($data, EXTR_SKIP); }
        $themeBase = realpath(dirname(__DIR__) . '/themes');
        $targetView = realpath($viewFile);
        if (!$targetView || !str_starts_with($targetView, $themeBase . DIRECTORY_SEPARATOR) || !is_file($targetView)) {
            error_log("SECURITY: blocked dynamic include: theme view");
            http_response_code(400);
            return 'Invalid theme view path';
        }
        ob_start();
        require_once $targetView;
        $content = ob_get_clean();
        $layoutFile = $baseDir . '/layout.php';
        if (file_exists($layoutFile)) {
            $targetLayout = realpath($layoutFile);
            if (!$targetLayout || !str_starts_with($targetLayout, $themeBase . DIRECTORY_SEPARATOR) || !is_file($targetLayout)) {
                error_log("SECURITY: blocked dynamic include: theme layout");
                http_response_code(400);
                return 'Invalid theme layout path';
            }
            ob_start();
            require_once $targetLayout;
            return ob_get_clean();
        }
        return $content;
    }
    /**
     * Load theme metadata from theme.json or cache
     * @param string $themeName Name of the theme to load
     * @param string $context Theme context (public/admin)
     * @return array|null Theme metadata or null on failure
     */
    public static function loadThemeMetadata(string $themeName, string $context = self::CONTEXT_PUBLIC): ?array {
        $cacheKey = $context . '_' . $themeName;
        
        if (isset(self::$themeCache[$cacheKey])) {
            return self::$themeCache[$cacheKey];
        }

        $themePath = ($context === self::CONTEXT_ADMIN ? "admin/themes/" : "themes/") . "$themeName/theme.json";
        
        if (!file_exists($themePath)) {
            // Check for parent theme if registered
            $registeredKey = $context . '_' . $themeName;
            if (isset(self::$registeredThemes[$registeredKey]['parent'])) {
                return self::loadThemeMetadata(self::$registeredThemes[$registeredKey]['parent'], $context);
            }
            trigger_error("Theme metadata file not found: $themePath", E_USER_WARNING);
            return null;
        }

        $json = file_get_contents($themePath);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            trigger_error("Invalid theme.json format in $themeName: " . json_last_error_msg(), E_USER_WARNING);
            return null;
        }

        // Merge with parent theme if exists
        if (isset($data['parent'])) {
            $parentData = self::loadThemeMetadata($data['parent'], $context);
            if ($parentData) {
                $data = array_merge($parentData, $data);
            }
        }

        self::$themeCache[$cacheKey] = $data;
        return $data;
    }

    /**
     * Clear theme cache
     * @param string|null $themeName Optional theme name to clear (default: all)
     * @param string|null $context Optional context to clear (default: all)
     */
    public static function clearCache(?string $themeName = null, ?string $context = null): void {
        if ($themeName && $context) {
            unset(self::$themeCache[$context . '_' . $themeName]);
        } elseif ($themeName) {
            foreach (array_keys(self::$themeCache) as $key) {
                if (str_ends_with($key, '_' . $themeName)) {
                    unset(self::$themeCache[$key]);
                }
            }
        } else {
            self::$themeCache = [];
        }
    }

    /**
     * Validate theme structure
     * @param string $themeName Name of the theme to validate
     * @param string $context Theme context (public/admin)
     * @return bool True if valid, false otherwise
     */
    public static function validateTheme(string $themeName, string $context = self::CONTEXT_PUBLIC): bool {
        $basePath = $context === self::CONTEXT_ADMIN ? "admin/themes/" : "themes/";
        $requiredFiles = [
            "{$basePath}{$themeName}/theme.json",
            "{$basePath}{$themeName}/layout.php",
            "{$basePath}{$themeName}/style.css"
        ];

        foreach ($requiredFiles as $file) {
            if (!file_exists($file)) {
                trigger_error("Missing required theme file: $file", E_USER_WARNING);
                return false;
            }
        }

        return true;
    }

    /**
     * Get database connection
     * @return \PDO|null Database connection or null if unavailable
     */
    private static function getDbConnection(): ?\PDO {
        try {
            return \core\Database::connection();
        } catch (\Exception $e) {
            error_log("ThemeManager: Database unavailable - " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get active theme from database
     * @param int $tenantId Tenant ID
     * @param string $context Theme context (public/admin)
     * @return string|null Theme name or null if not found
     */
    private static function getThemeFromDb(int $tenantId, string $context = self::CONTEXT_PUBLIC): ?string {
        $db = self::getDbConnection();
        if (!$db) return null;

        try {
            // Use system_settings table for public themes
            if ($context === self::CONTEXT_PUBLIC) {
                $stmt = $db->prepare("
                    SELECT active_theme FROM system_settings
                    WHERE tenant_id = :tenant_id OR tenant_id IS NULL
                    ORDER BY tenant_id DESC
                    LIMIT 1
                ");
                $stmt->execute([':tenant_id' => $tenantId]);
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                return $result['active_theme'] ?? null;
            }
            
            // Admin theme from settings table
            $stmt = $db->prepare("
                SELECT value FROM settings
                WHERE `key` = 'admin_theme'
                LIMIT 1
            ");
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result['value'] ?? null;
        } catch (\PDOException $e) {
            error_log("ThemeManager: Failed to get theme - " . $e->getMessage());
            return null;
        }
    }

    /**
     * Store theme in database
     * @param string $themeName Theme name to store
     * @param int $tenantId Tenant ID
     * @param string $context Theme context (public/admin)
     * @return bool True if stored successfully
     */
    private static function storeThemeInDb(string $themeName, int $tenantId, string $context = self::CONTEXT_PUBLIC): bool {
        $db = self::getDbConnection();
        if (!$db) return false;

        try {
            // Use system_settings for public themes
            if ($context === self::CONTEXT_PUBLIC) {
                $stmt = $db->prepare("
                    UPDATE system_settings SET active_theme = :theme_name
                    WHERE tenant_id = :tenant_id OR tenant_id IS NULL
                ");
                $result = $stmt->execute([
                    ':tenant_id' => $tenantId,
                    ':theme_name' => $themeName
                ]);
                if ($stmt->rowCount() === 0) {
                    $stmt = $db->prepare("
                        INSERT INTO system_settings (site_title, active_theme, tenant_id)
                        VALUES ('My CMS Site', :theme_name, :tenant_id)
                    ");
                    return $stmt->execute([
                        ':tenant_id' => $tenantId,
                        ':theme_name' => $themeName
                    ]);
                }
                return $result;
            }
            
            // Admin theme in settings table
            $stmt = $db->prepare("
                INSERT INTO settings (`key`, value, group_name)
                VALUES ('admin_theme', :theme_name, 'theme')
                ON DUPLICATE KEY UPDATE value = :theme_name2
            ");
            return $stmt->execute([
                ':theme_name' => $themeName,
                ':theme_name2' => $themeName
            ]);
        } catch (\PDOException $e) {
            error_log("ThemeManager: Failed to store theme - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get active theme name
     * @param int $tenantId Tenant ID
     * @param string $context Theme context (public/admin)
     * @return string|null Theme name or null if not found
     */
    public static function getActiveTheme(int $tenantId, string $context = self::CONTEXT_PUBLIC): ?string {
        // Support theme preview via ?theme= parameter (public context only)
        if ($context === self::CONTEXT_PUBLIC && isset($_GET['theme'])) {
            $previewTheme = preg_replace('/[^a-z0-9_-]/', '', strtolower($_GET['theme']));
            if ($previewTheme && self::validateTheme($previewTheme, $context)) {
                return $previewTheme;
            }
        }
        
        $theme = self::getThemeFromDb($tenantId, $context);
        if ($theme && self::validateTheme($theme, $context)) {
            return $theme;
        }
        return null;
    }

    /**
     * Set active theme
     * @param string $themeName Theme name to activate
     * @param int $tenantId Tenant ID
     * @param string $context Theme context (public/admin)
     * @return bool True if theme was activated successfully
     */
    public static function setActiveTheme(string $themeName, int $tenantId, string $context = self::CONTEXT_PUBLIC): bool {
        if (!self::validateTheme($themeName, $context)) {
            return false;
        }
        return self::storeThemeInDb($themeName, $tenantId, $context);
    }

    /**
     * Get list of available themes
     * @param string $context Theme context (public/admin)
     * @return array List of theme names
     */
    public static function getAvailableThemes(string $context = self::CONTEXT_PUBLIC): array {
        $themes = [];
        $basePath = $context === self::CONTEXT_ADMIN ? 'admin/themes/' : 'themes/';
        
        foreach (glob($basePath . '*', GLOB_ONLYDIR) as $dir) {
            $themeName = basename($dir);
            if (self::validateTheme($themeName, $context)) {
                $themes[] = $themeName;
            }
        }
        
        return $themes;
    }

    /**
     * Apply active theme
     * @param string $themeName Name of the theme to apply
     * @param int $tenantId Tenant ID (default: current tenant)
     * @param string $context Theme context (public/admin)
     * @return bool True if applied successfully, false otherwise
     */
    public static function applyTheme(string $themeName, int $tenantId = null, string $context = self::CONTEXT_PUBLIC): bool {
        if (!self::validateTheme($themeName, $context)) {
            return false;
        }

        // Try DB storage first
        $tenantId = $tenantId ?? self::getCurrentTenantId();
        $dbSuccess = self::storeThemeInDb($themeName, $tenantId, $context);

        // Fallback to session if DB fails
        if (!$dbSuccess) {
            $_SESSION['active_theme'] = $themeName;
        }

        return true;
    }
}
