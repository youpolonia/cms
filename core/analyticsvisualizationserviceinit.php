<?php
declare(strict_types=1);

require_once __DIR__ . '/analyticsvisualizationservice.php';

class AnalyticsVisualizationServiceInit {
    public static function register(): void {
        DependencyContainer::registerService(
            'analytics_visualization',
            function() {
                return new AnalyticsVisualizationService();
            }
        );
    }
}

AnalyticsVisualizationServiceInit::register();
