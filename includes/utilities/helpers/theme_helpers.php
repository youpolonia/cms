<?php
declare(strict_types=1);

/**
 * Theme Helper Functions
 */
namespace Theme;

use Core\TenantManager;
use Includes\WidgetManager;

/**
 * Render all widgets in a specified region
 * 
 * @param string $region The widget region identifier
 * @return string HTML output of all widgets in the region
 */
function render_widget_region(string $region, ?string $theme = null): string
{
    $output = '';
    try {
        $tenantId = TenantManager::getCurrentTenantId();
        
        // Get all widget bindings for this region
        $bindings = WidgetManager::getWidgetsForRegion($tenantId, $region, $theme);
        
        // Sort widgets by position (default to 0 if not set)
        usort($bindings, function($a, $b) {
            $posA = $a['settings']['position'] ?? 0;
            $posB = $b['settings']['position'] ?? 0;
            return $posA <=> $posB;
        });
        
        // Render each widget if visible
        foreach ($bindings as $widget) {
            $widgetId = $widget['widget_name'];
            $settings = $widget['settings'] ?? [];
            
            // Skip if visibility conditions not met
            if (isset($settings['visibility_conditions'])) {
                if (!evaluate_visibility_conditions($settings['visibility_conditions'])) {
                    continue;
                }
            }
            
            $output .= WidgetManager::renderWidget(
                $widgetId,
                ['region_settings' => $settings],
                (string)$tenantId,
                $settings
            );
        }
    } catch (\Exception $e) {
        error_log("Widget region render failed: " . $e->getMessage());
        // Graceful fallback - return empty string
    }

    return $output;
}

/**
 * Evaluate widget visibility conditions
 * Supported condition types:
 * - date_range: Show between start/end dates
 * - time_range: Show between start/end times
 * - user_role: Show for specific user roles
 * - logged_in: Show only when logged in/out
 */
function evaluate_visibility_conditions(array $conditions): bool
{
    try {
        foreach ($conditions as $condition) {
            if (!isset($condition['type'])) {
                continue;
            }

            switch ($condition['type']) {
                case 'date_range':
                    $now = time();
                    $start = strtotime($condition['start'] ?? 'now');
                    $end = strtotime($condition['end'] ?? 'now');
                    if ($now < $start || $now > $end) {
                        return false;
                    }
                    break;

                case 'time_range':
                    $now = strtotime(date('H:i'));
                    $start = strtotime($condition['start'] ?? '00:00');
                    $end = strtotime($condition['end'] ?? '23:59');
                    if ($now < $start || $now > $end) {
                        return false;
                    }
                    break;

                case 'user_role':
                    $userRoles = UserManager::getCurrentUserRoles();
                    if (!in_array($condition['role'], $userRoles)) {
                        return false;
                    }
                    break;

                case 'logged_in':
                    $isLoggedIn = UserManager::isLoggedIn();
                    if ($condition['value'] && !$isLoggedIn) {
                        return false;
                    }
                    if (!$condition['value'] && $isLoggedIn) {
                        return false;
                    }
                    break;
            }
        }
    } catch (\Exception $e) {
        error_log("Visibility condition evaluation failed: " . $e->getMessage());
        // Fail safe - show widget if evaluation fails
        return true;
    }

    return true;
}
