<?php

namespace Includes\Multisite;

use Includes\Database\DatabaseConnection;
use Includes\Services\AnalyticsService;

/**
 * CrossSiteAnalytics - Handles analytics aggregation across multiple sites
 */
class CrossSiteAnalytics
{
    /**
     * @var SiteManager
     */
    private SiteManager $siteManager;
    
    /**
     * @var DatabaseConnection
     */
    private DatabaseConnection $db;
    
    /**
     * @var AnalyticsService
     */
    private AnalyticsService $analyticsService;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->siteManager = new SiteManager();
        $this->db = \core\Database::connection();
        $this->analyticsService = new AnalyticsService();
    }
    
    /**
     * Get cross-site report for specified sites
     *
     * @param array $siteIds
     * @param array $dateRange
     * @param string $groupBy
     * @return array
     */
    public function getCrossSiteReport(array $siteIds, array $dateRange, string $groupBy = 'day'): array
    {
        // Validate sites
        $validSites = [];
        foreach ($siteIds as $siteId) {
            if ($this->siteManager->siteExists($siteId)) {
                $validSites[] = $siteId;
            }
        }
        
        if (empty($validSites)) {
            return ['error' => 'No valid sites specified'];
        }
        
        // Prepare date range
        $startDate = $dateRange['start'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $dateRange['end'] ?? date('Y-m-d');
        
        // Prepare group by clause
        $groupByClause = $this->getGroupByClause($groupBy);
        
        // Build query
        $placeholders = implode(',', array_fill(0, count($validSites), '?'));
        $query = "
            SELECT 
                site_id,
                $groupByClause AS time_period,
                COUNT(*) AS event_count,
                COUNT(DISTINCT user_id) AS unique_users
            FROM 
                analytics_events
            WHERE 
                site_id IN ($placeholders)
                AND created_at BETWEEN ? AND ?
            GROUP BY 
                site_id, time_period
            ORDER BY 
                site_id, time_period
        ";
        
        // Execute query
        $params = array_merge($validSites, [$startDate, $endDate]);
        $results = $this->db->query($query, $params)->fetchAll();
        
        // Format results
        $report = [
            'date_range' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'group_by' => $groupBy,
            'sites' => $validSites,
            'data' => []
        ];
        
        // Organize data by site
        foreach ($results as $row) {
            if (!isset($report['data'][$row['site_id']])) {
                $report['data'][$row['site_id']] = [];
            }
            
            $report['data'][$row['site_id']][] = [
                'period' => $row['time_period'],
                'events' => $row['event_count'],
                'users' => $row['unique_users']
            ];
        }
        
        return $report;
    }
    
    /**
     * Get top content across all sites
     *
     * @param int $limit
     * @param array $dateRange
     * @return array
     */
    public function getTopContentAcrossSites(int $limit = 10, array $dateRange = []): array
    {
        // Prepare date range
        $startDate = $dateRange['start'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $dateRange['end'] ?? date('Y-m-d');
        
        // Get all sites
        $sites = $this->siteManager->getAllSites();
        $siteIds = array_keys($sites);
        
        // Build query
        $placeholders = implode(',', array_fill(0, count($siteIds), '?'));
        $query = "
            SELECT 
                site_id,
                content_id,
                COUNT(*) AS view_count
            FROM 
                analytics_events
            WHERE 
                site_id IN ($placeholders)
                AND event_type = 'page_view'
                AND created_at BETWEEN ? AND ?
            GROUP BY 
                site_id, content_id
            ORDER BY 
                view_count DESC
            LIMIT ?
        ";
        
        // Execute query
        $params = array_merge($siteIds, [$startDate, $endDate, $limit]);
        $results = $this->db->query($query, $params)->fetchAll();
        
        // Format results
        $topContent = [
            'date_range' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'items' => []
        ];
        
        // Get content details for each result
        foreach ($results as $row) {
            // Switch to site context to get content
            $originalSite = $this->siteManager->getCurrentSite();
            $this->siteManager->setCurrentSite($row['site_id']);
            
            // Get content details
            $content = $this->getContentDetails($row['content_id']);
            
            // Switch back to original site
            $this->siteManager->setCurrentSite($originalSite);
            
            if ($content) {
                $topContent['items'][] = [
                    'site_id' => $row['site_id'],
                    'site_name' => $sites[$row['site_id']]['domain'] ?? $row['site_id'],
                    'content_id' => $row['content_id'],
                    'title' => $content['title'] ?? 'Unknown',
                    'slug' => $content['slug'] ?? '',
                    'view_count' => $row['view_count']
                ];
            }
        }
        
        return $topContent;
    }
    
    /**
     * Get content details
     *
     * @param int $contentId
     * @return array|null
     */
    private function getContentDetails(int $contentId): ?array
    {
        $query = "
            SELECT id, title, slug
            FROM contents
            WHERE id = ?
        ";
        
        $result = $this->db->query($query, [$contentId])->fetch();
        
        return $result ?: null;
    }
    
    /**
     * Get SQL clause for grouping by time period
     *
     * @param string $groupBy
     * @return string
     */
    private function getGroupByClause(string $groupBy): string
    {
        switch ($groupBy) {
            case 'hour':
                return "DATE_FORMAT(created_at, '%Y-%m-%d %H:00')";
            case 'day':
                return "DATE(created_at)";
            case 'week':
                return "DATE_FORMAT(created_at, '%Y-%u')";
            case 'month':
                return "DATE_FORMAT(created_at, '%Y-%m')";
            case 'year':
                return "YEAR(created_at)";
            default:
                return "DATE(created_at)";
        }
    }
    
    /**
     * Get site comparison report
     *
     * @param array $siteIds
     * @param array $metrics
     * @param array $dateRange
     * @return array
     */
    public function getSiteComparisonReport(array $siteIds, array $metrics, array $dateRange = []): array
    {
        // Validate sites
        $validSites = [];
        foreach ($siteIds as $siteId) {
            if ($this->siteManager->siteExists($siteId)) {
                $validSites[] = $siteId;
            }
        }
        
        if (empty($validSites)) {
            return ['error' => 'No valid sites specified'];
        }
        
        // Prepare date range
        $startDate = $dateRange['start'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $dateRange['end'] ?? date('Y-m-d');
        
        // Prepare metrics
        $validMetrics = $this->getValidMetrics($metrics);
        
        // Build query
        $placeholders = implode(',', array_fill(0, count($validSites), '?'));
        $metricSelects = [];
        
        foreach ($validMetrics as $metric) {
            $metricSelects[] = $this->getMetricSqlExpression($metric);
        }
        
        $metricSelectsStr = implode(', ', $metricSelects);
        
        $query = "
            SELECT 
                site_id,
                $metricSelectsStr
            FROM 
                analytics_events
            WHERE 
                site_id IN ($placeholders)
                AND created_at BETWEEN ? AND ?
            GROUP BY 
                site_id
        ";
        
        // Execute query
        $params = array_merge($validSites, [$startDate, $endDate]);
        $results = $this->db->query($query, $params)->fetchAll();
        
        // Format results
        $report = [
            'date_range' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'metrics' => $validMetrics,
            'sites' => []
        ];
        
        // Get site details and organize data
        foreach ($results as $row) {
            $siteConfig = $this->siteManager->getSiteConfig($row['site_id']);
            
            $siteData = [
                'site_id' => $row['site_id'],
                'domain' => $siteConfig['domain'] ?? $row['site_id'],
                'metrics' => []
            ];
            
            foreach ($validMetrics as $metric) {
                $siteData['metrics'][$metric] = $row[$metric] ?? 0;
            }
            
            $report['sites'][] = $siteData;
        }
        
        return $report;
    }
    
    /**
     * Get valid metrics
     *
     * @param array $metrics
     * @return array
     */
    private function getValidMetrics(array $metrics): array
    {
        $allowedMetrics = [
            'page_views',
            'unique_visitors',
            'bounce_rate',
            'avg_session_duration',
            'conversion_rate'
        ];
        
        return array_intersect($allowedMetrics, $metrics);
    }
    
    /**
     * Get SQL expression for a metric
     *
     * @param string $metric
     * @return string
     */
    private function getMetricSqlExpression(string $metric): string
    {
        switch ($metric) {
            case 'page_views':
                return "COUNT(CASE WHEN event_type = 'page_view' THEN 1 END) AS page_views";
            case 'unique_visitors':
                return "COUNT(DISTINCT user_id) AS unique_visitors";
            case 'bounce_rate':
                return "
                    (COUNT(CASE WHEN event_type = 'session_start' AND session_pages = 1 THEN 1 END) / 
                    COUNT(CASE WHEN event_type = 'session_start' THEN 1 END)) * 100 AS bounce_rate
                ";
            case 'avg_session_duration':
                return "
                    AVG(CASE WHEN event_type = 'session_end' THEN session_duration ELSE NULL END) AS avg_session_duration
                ";
            case 'conversion_rate':
                return "
                    (COUNT(CASE WHEN event_type = 'conversion' THEN 1 END) / 
                    COUNT(DISTINCT user_id)) * 100 AS conversion_rate
                ";
            default:
                return "COUNT(*) AS $metric";
        }
    }
}
