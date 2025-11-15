<?php

namespace Includes\Analytics;

use Includes\Database\Connection;
use Includes\Services\TenantService;

class EventProcessor
{
    protected $db;
    protected $tenantService;

    public function __construct()
    {
        $this->db = Connection::getInstance();
        $this->tenantService = new TenantService();
    }

    public function getDailySummary(string $date): array
    {
        $query = "SELECT event_type, COUNT(*) as count 
                  FROM analytics_events 
                  WHERE DATE(created_at) = :date
                  GROUP BY event_type";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':date' => $date]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getTenantSummary(string $tenantId, array $dateRange): array
    {
        if (!$this->tenantService->tenantExists($tenantId)) {
            throw new \InvalidArgumentException("Invalid tenant ID");
        }

        $query = "SELECT event_type, COUNT(*) as count 
                  FROM tenant_analytics 
                  WHERE tenant_id = :tenantId 
                  AND created_at BETWEEN :start AND :end
                  GROUP BY event_type";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':tenantId' => $tenantId,
            ':start' => $dateRange['start'],
            ':end' => $dateRange['end']
        ]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getComparisonReport(array $tenantIds, array $dateRange): array
    {
        $placeholders = implode(',', array_fill(0, count($tenantIds), '?'));
        $query = "SELECT tenant_id, event_type, COUNT(*) as count 
                  FROM tenant_analytics 
                  WHERE tenant_id IN ($placeholders)
                  AND created_at BETWEEN ? AND ?
                  GROUP BY tenant_id, event_type";
        
        $params = array_merge($tenantIds, [$dateRange['start'], $dateRange['end']]);
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function processDaily(): void
    {
        // Process and aggregate daily analytics
        $this->archiveDailyEvents();
        $this->generateDailyReports();
    }

    protected function archiveDailyEvents(): void
    {
        // Implementation for archiving events
    }

    protected function generateDailyReports(): void
    {
        // Implementation for generating reports
    }
}
