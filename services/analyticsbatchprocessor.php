<?php
declare(strict_types=1);

class AnalyticsBatchProcessor {
    public static function runScheduledBatch(string $level): void {
        $tenants = TenantManager::getActiveTenants();
        $batchSize = Config::get('analytics.batch_size', 1000);
        
        foreach ($tenants as $tenantId) {
            $events = EventRepository::getUnprocessedEvents($tenantId, $level, $batchSize);
            if (!empty($events)) {
                $result = AnalyticsAggregator::processBatch($tenantId, $events, $level);
                EventRepository::markEventsProcessed($events, $level);
                AggregatedDataStore::save($result);
            }
        }
    }

    public static function processCustomBatch(array $tenantIds, string $level, int $limit = 1000): array {
        $results = [];
        foreach ($tenantIds as $tenantId) {
            $events = EventRepository::getUnprocessedEvents($tenantId, $level, $limit);
            if (!empty($events)) {
                $results[$tenantId] = AnalyticsAggregator::processBatch($tenantId, $events, $level);
            }
        }
        return $results;
    }
}
