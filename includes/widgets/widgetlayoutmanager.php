<?php
/**
 * WidgetLayoutManager - Handles widget position sorting and visibility conditions
 * 
 * FTP-Compatible Implementation - No CLI dependencies
 */
class WidgetLayoutManager
{
    /**
     * Sort widgets by their position attribute
     * 
     * @param array $widgets Array of widgets with position attributes
     * @return array Sorted widgets array
     */
    public static function sortWidgetsByPosition(array $widgets): array
    {
        usort($widgets, function($a, $b) {
            return $a['position'] <=> $b['position'];
        });
        return $widgets;
    }

    /**
     * Check if a widget should be visible based on conditions and context
     * 
     * @param array $widget Widget data including conditions
     * @param array $context Current user/application context
     * @return bool Whether widget should be visible
     */
    public static function checkVisibility(array $widget, array $context): bool
    {
        if (empty($widget['conditions'])) {
            return true;
        }

        foreach ($widget['conditions'] as $key => $value) {
            if (!isset($context[$key]) || $context[$key] !== $value) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get visible widgets in correct position order
     * 
     * @param array $widgets All widgets to process
     * @param array $context Current user/application context
     * @return array Filtered and sorted widgets
     */
    public static function getVisibleWidgets(array $widgets, array $context): array
    {
        $visibleWidgets = [];
        foreach ($widgets as $widget) {
            if (self::checkVisibility($widget, $context)) {
                $visibleWidgets[] = $widget;
            }
        }
        return self::sortWidgetsByPosition($visibleWidgets);
    }
}
