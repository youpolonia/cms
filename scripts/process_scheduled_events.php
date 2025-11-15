#!/usr/bin/env php
<?php

require_once __DIR__ . '/../includes/services/SchedulingService.php';

use Includes\services\SchedulingService;

try {
    $service = new SchedulingService();
    $processedCount = $service->processDueEvents();
    
    echo "Processed $processedCount scheduled events\n";
    exit(0);
} catch (Exception $e) {
    error_log("Error processing scheduled events: " . $e->getMessage());
    exit(1);
}
