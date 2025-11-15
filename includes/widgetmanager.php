<?php

require_once __DIR__ . '/interfaces/widgetinterface.php';

class WidgetManager
{
    /**
     * @var array Registered widgets [widget_id => WidgetInterface]
     */
    private static array $widgets = [];

    /**
     * Get all registered widgets
     * @return array [widget_id => WidgetInterface]
     */
    public static function getRegisteredWidgets(): array
    {
        return self::$widgets;
    }

    /**
     * Register a widget implementation
     * @param string $widgetId Unique widget identifier
     * @param WidgetInterface $widget Widget implementation
     */
    public static function registerWidget(string $widgetId, WidgetInterface $widget): void
    {
        self::$widgets[$widgetId] = $widget;
    }

    /**
     * Load widgets from directory
     * @param string $directory Path to widgets directory
     */
    public static function loadWidgetsFromDirectory(string $directory): void
    {
        foreach (glob($directory . '/*.php') as $file) {
            $base = realpath(__DIR__ . '/widgets');
            $target = realpath($file);
            if ($base === false || $target === false || substr_compare($target, $base . DIRECTORY_SEPARATOR, 0, strlen($base) + 1) !== 0 || !is_file($target)) {
                error_log("SECURITY: blocked dynamic include: widget loader");
                continue;
            }
            require_once $target;

            $className = basename($file, '.php');
            if (class_exists($className)) {
                $widget = new $className();
                if ($widget instanceof WidgetInterface) {
                    self::registerWidget($className, $widget);
                }
            }
        }
    }

    /**
     * Get widgets bound to a specific region
     * @param int $tenantId Tenant ID
     * @param string $region Region identifier
     * @return array Array of widget bindings [widget_id => settings]
     */
    public static function getWidgetsForRegion(int $tenantId, string $region, ?string $theme = null): array
    {
        return RegionBindingManager::get_bindings_by_region($tenantId, $region, $theme);
    }

    /**
     * Render all widgets for a specific region
     * @param int $tenantId Tenant ID
     * @param string $region Region identifier
     * @param array $context Rendering context
     * @return string Combined HTML output of all widgets in region
     */
    public static function renderRegionWidgets(
        int $tenantId,
        string $region,
        array $context = [],
        ?string $theme = null
    ): string {
        $output = '';
        $bindings = self::getWidgetsForRegion($tenantId, $region, $theme);
        
        foreach ($bindings as $widgetId => $settings) {
            $widgetContext = array_merge($context, [
                'region_settings' => $settings
            ]);
            $output .= self::renderWidget($widgetId, $widgetContext, (string)$tenantId);
        }
        
        return $output;
    }

    /**
     * Render a widget by ID with optional context and region settings
     * @param string $widgetId Widget identifier
     * @param array $context Rendering context
     * @param string|null $tenantId Current tenant ID
     * @param array|null $regionSettings Optional region-specific settings
     * @return string Rendered HTML or empty string if not found/available
     */
    public static function renderWidget(
        string $widgetId,
        array $context = [],
        ?string $tenantId = null,
        ?array $regionSettings = null
    ): string {
        if (!isset(self::$widgets[$widgetId])) {
            return '';
        }

        $widget = self::$widgets[$widgetId];

        if ($tenantId !== null && !$widget->isAvailableForTenant($tenantId)) {
            return '';
        }

        // Merge region settings if provided
        if ($regionSettings !== null) {
            $context = array_merge($context, ['region_settings' => $regionSettings]);
        }

        // Check for theme-specific template first
        $templatePath = ThemeManager::getWidgetTemplate($widgetId);
        if ($templatePath) {
            ob_start();
            extract($context);
            $base = realpath(__DIR__ . '/../public/themes');
            $target = realpath($templatePath);
            if ($base === false || $target === false || substr_compare($target, $base . DIRECTORY_SEPARATOR, 0, strlen($base) + 1) !== 0 || !is_file($target)) {
                error_log("SECURITY: blocked dynamic include: widget template");
                ob_end_clean();
                return '';
            }
            require_once $target;
            return ob_get_clean();
        }

        return $widget->render($context);
    }

    /**
     * Toggle widget status (enabled/disabled) by ID
     * @param int $widgetId Widget ID to toggle
     * @return array Array with status information ['status' => boolean] or false if not found
     */
    public function toggleWidgetStatus($widgetId): array|false
    {
        try {
            $db = \core\Database::connection();
            
            // First, get current enabled state
            $stmt = $db->prepare("SELECT enabled FROM widget_settings WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $widgetId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                error_log("Widget not found: " . $widgetId);
                return false;
            }
            
            // Toggle the status
            $currentStatus = (bool)($result['enabled'] ?? false);
            $newStatus = !$currentStatus;
            
            // Update the widget status
            $updateStmt = $db->prepare("UPDATE widget_settings SET enabled = :enabled WHERE id = :id");
            $updateResult = $updateStmt->execute([
                ':enabled' => $newStatus ? 1 : 0,
                ':id' => $widgetId
            ]);
            
            if ($updateResult && $updateStmt->rowCount() > 0) {
                return ['status' => $newStatus];
            } else {
                error_log("Failed to update widget status for ID: " . $widgetId);
                return false;
            }
            
        } catch (PDOException $e) {
            error_log("Database error in toggleWidgetStatus: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Error in toggleWidgetStatus: " . $e->getMessage());
            return false;
        }
    }

    // Example Usage:
    /*
    // Register widgets from directory
    WidgetManager::loadWidgetsFromDirectory(__DIR__ . '/widgets');
    
    // Render all widgets in a region
    $output = WidgetManager::renderRegionWidgets(
        tenantId: 1,
        region: 'sidebar',
        context: ['current_page' => 'home']
    );
    
    // Render single widget with region settings
    $output = WidgetManager::renderWidget(
        widgetId: 'news_feed',
        context: ['limit' => 5],
        tenantId: '1',
        regionSettings: ['position' => 'top']
    );
    */
}
