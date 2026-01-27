<?php
/**
 * Widget rendering functionality for CMS
 * Implements widget region rendering with sorting and visibility conditions
 */

class WidgetRenderer {
    /**
     * Render widgets for a region with tenant isolation
     * 
     * @param PDO $pdo Database connection
     * @param string $regionId The widget region ID
     * @param string|null $tenantId Current tenant ID (null for shared widgets)
     * @return string HTML output of rendered widgets
     */
    public static function render_widget_region(PDO $pdo, string $regionId, ?string $tenantId = null): string {
        $output = '';
        
        try {
            // Get widgets for this region, ordered by position
            $stmt = $pdo->prepare("
                SELECT widget_id, content, visibility_conditions 
                FROM widget_region_bindings 
                WHERE region_id = :regionId 
                AND (tenant_id IS NULL OR tenant_id = :tenantId)
                ORDER BY position ASC
            ");
            $stmt->execute([':regionId' => $regionId, ':tenantId' => $tenantId]);
            $widgets = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($widgets as $widget) {
                // Skip if visibility conditions fail
                if (!self::check_visibility_conditions($widget['visibility_conditions'])) {
                    continue;
                }

                $output .= $widget['content'];
            }
        } catch (PDOException $e) {
            error_log("Widget rendering error: " . $e->getMessage());
        }

        return $output;
    }

    /**
     * Check widget visibility conditions
     *
     * @param string|null $conditions JSON string of visibility conditions
     * @return bool True if widget should be visible
     *
     * Condition format examples:
     * {
     *   "roles": ["admin", "editor"], // Current user must have one of these roles
     *   "time": {
     *     "start": "09:00", // Only show between these times
     *     "end": "17:00"
     *   },
     *   "date": {
     *     "after": "2025-01-01", // Only show after this date
     *     "before": "2025-12-31" // Only show before this date
     *   }
     * }
     */
    private static function check_visibility_conditions(?string $conditions): bool {
        if (empty($conditions)) {
            return true; // No conditions = always visible
            /**
             * Example usage:
             *
             * $html = WidgetRenderer::render_widget_region(
             *     $pdo,
             *     'sidebar',
             *     $_SESSION['tenant_id'] ?? null
             * );
             * echo $html;
             */
        }

        try {
            $decoded = json_decode($conditions, true, 512, JSON_THROW_ON_ERROR);
            
            // Check role conditions if present
            if (isset($decoded['roles'])) {
                $userRoles = $_SESSION['user_roles'] ?? [];
                if (empty(array_intersect($decoded['roles'], $userRoles))) {
                    return false;
                }
            }

            // Check time window if present
            if (isset($decoded['time'])) {
                $now = date('H:i');
                if (isset($decoded['time']['start']) && $now < $decoded['time']['start']) {
                    return false;
                }
                if (isset($decoded['time']['end']) && $now > $decoded['time']['end']) {
                    return false;
                }
            }

            // Check date range if present
            if (isset($decoded['date'])) {
                $today = date('Y-m-d');
                if (isset($decoded['date']['after']) && $today < $decoded['date']['after']) {
                    return false;
                }
                if (isset($decoded['date']['before']) && $today > $decoded['date']['before']) {
                    return false;
                }
            }

            return true;
            
        } catch (JsonException $e) {
            error_log("Invalid widget visibility conditions: " . $e->getMessage());
            return false; // Hide widget if conditions are invalid
        }
    }
}
