<?php

class AnalyticsService {
    private static $instance;
    private $storage;
    
    private function __construct() {
        $this->storage = $this->initStorage();
    }
    
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function initStorage() {
        $config = require_once __DIR__ . '/../config/analytics.php';
        $storageType = $config['real_time']['storage']['primary'] ?? 'database';
        
        switch ($storageType) {
            case 'redis':
                return new RedisStorage();
            case 'database':
            default:
                return new DatabaseStorage();
        }
    }
    
    public function getEngagementMetrics(string $tenantId, array $filters = []) {
        return $this->storage->getMetrics($tenantId, [
            'views', 'sessions', 'duration'
        ], $filters);
    }
    
    public function getContentPerformance(string $tenantId, array $filters = []) {
        return $this->storage->getMetrics($tenantId, [
            'content_type', 'views', 'unique_views'
        ], $filters);
    }
    
    public function generateReport(string $tenantId, array $params) {
        $reportData = $this->storage->getReportData($tenantId, $params);
        return $this->formatReport($reportData, $params['format'] ?? 'json');
    }
    
    private function formatReport(array $data, string $format) {
        switch ($format) {
            case 'csv':
                return $this->formatAsCsv($data);
            case 'pdf':
                return $this->formatAsPdf($data);
            case 'json':
            default:
                return json_encode($data);
        }
    }
    
    // ... additional private helper methods
}

interface AnalyticsStorage {
    public function getMetrics(string $tenantId, array $metrics, array $filters): array;
    public function getReportData(string $tenantId, array $params): array;
}

class DatabaseStorage implements AnalyticsStorage {
    public function getMetrics(string $tenantId, array $metrics, array $filters): array {
        // Implementation for database storage
    }
    
    public function getReportData(string $tenantId, array $params): array {
        // Implementation for report generation
    }
}

class RedisStorage implements AnalyticsStorage {
    public function getMetrics(string $tenantId, array $metrics, array $filters): array {
        // Implementation for Redis storage
    }
    
    public function getReportData(string $tenantId, array $params): array {
        // Implementation for report generation
    }
}
