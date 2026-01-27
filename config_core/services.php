<?php

use App\Services\AnalyticsService;
use App\Services\PerformanceMonitor;
use App\Models\Site;
use Includes\DependencyContainer;

return function(DependencyContainer $container) {
    // Register AnalyticsService
    $container->register('analytics', function() {
        return new AnalyticsService(
            Site::getCurrent(),
            \core\Database::connection()
        );
    });

    // Register PerformanceMonitor
    $container->register('performance', function() {
        return new PerformanceMonitor(
            Site::getCurrent(),
            \core\Database::connection()
        );
    });
};
