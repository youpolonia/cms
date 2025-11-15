<?php

require_once __DIR__ . '/../includes/dependencycontainer.php';
require_once __DIR__ . '/analyticsservice.php';

use Includes\DependencyContainer;
use Services\AnalyticsService;

class AnalyticsInitializer
{
    public static function init(PDO $db): void
    {
        AnalyticsService::register($db);
    }
}
