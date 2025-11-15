<?php

use Includes\Analytics\EventProcessor;
use Includes\Routing\Response;

add_route('GET', '/analytics/summary', function() {
    $date = $_GET['date'] ?? date('Y-m-d');
    $processor = new EventProcessor();
    
    try {
        $summary = $processor->getDailySummary($date);
        return Response::json($summary);
    } catch (Exception $e) {
        return Response::error('Failed to fetch analytics data', 500);
    }
});

add_route('POST', '/analytics/process', function() {
    if (!isset($_SERVER['HTTP_X_CRON_TOKEN']) || $_SERVER['HTTP_X_CRON_TOKEN'] !== getenv('CRON_SECRET')) {
        return Response::error('Unauthorized', 401);
    }

    $processor = new EventProcessor();
    $processor->processDaily();
    
    return Response::json(['status' => 'success']);
});

add_route('GET', '/analytics/export', function() {
    $format = $_GET['format'] ?? 'csv';
    $range = $_GET['range'] ?? '7d';
    
    if (!in_array($format, ['csv', 'json'])) {
        return Response::error('Invalid export format', 400);
    }

    try {
        require_once __DIR__.'/../../services/analytics/Aggregator.php';
        $processor = new EventProcessor();
        $data = $processor->getRangeData($range);
        
        if ($format === 'csv') {
            Aggregator::exportToCsv($data, "analytics_export_{$range}.csv");
        } else {
            Aggregator::exportToJson($data, "analytics_export_{$range}.json", true);
        }
        
    } catch (Exception $e) {
        return Response::error('Export failed: '.$e->getMessage(), 500);
    }
});
