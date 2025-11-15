<?php

namespace Includes\Services;

use Includes\Database\DatabaseConnection;
use Includes\Session\SessionManager;

/**
 * AnalyticsService provides functionality for tracking and analyzing user behavior
 */
class AnalyticsService {
    /**
     * Track an analytics event
     *
     * @param string $eventType Type of event
     * @param array $data Event data
     * @param int|null $userId User ID (null for anonymous users)
     * @return bool Success status
     */
    public static function trackEvent(string $eventType, array $data = [], ?int $userId = null): bool {
        $session = SessionManager::getInstance();
        
        // Use provided user ID or get from session
        $userId = $userId ?? $session->get('user_id') ?? 0;
        $sessionId = $session->getId();
        
        $query = "
            INSERT INTO analytics_events
            (event_type, user_id, session_id, data, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ";
        
        return DatabaseConnection::execute($query, [
            $eventType,
            $userId,
            $sessionId,
            json_encode($data)
        ]);
    }
    
    /**
     * Get recommendation analytics
     *
     * @param array $filters Filters to apply
     * @param string $timeframe Timeframe to analyze (day, week, month)
     * @return array Analytics data
     */
    public static function getRecommendationAnalytics(array $filters = [], string $timeframe = 'week'): array {
        // Determine date range based on timeframe
        $dateRange = self::getDateRangeForTimeframe($timeframe);
        
        // Build query conditions from filters
        $conditions = ["created_at BETWEEN ? AND ?"];
        $params = [$dateRange['start'], $dateRange['end']];
        
        if (isset($filters['user_id'])) {
            $conditions[] = "user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (isset($filters['event_type'])) {
            $conditions[] = "event_type = ?";
            $params[] = $filters['event_type'];
        }
        
        $whereClause = implode(' AND ', $conditions);
        
        // Get impression and click counts
        $query = "
            SELECT 
                DATE(created_at) as date,
                event_type,
                COUNT(*) as count
            FROM 
                analytics_events
            WHERE 
                $whereClause
                AND event_type IN ('impression', 'content_click')
            GROUP BY 
                DATE(created_at), event_type
            ORDER BY 
                date ASC
        ";
        
        $results = DatabaseConnection::fetchAll($query, $params);
        
        // Process results into a more usable format
        $analytics = [
            'timeframe' => $timeframe,
            'start_date' => $dateRange['start'],
            'end_date' => $dateRange['end'],
            'daily' => [],
            'totals' => [
                'impressions' => 0,
                'clicks' => 0,
                'ctr' => 0
            ]
        ];
        
        // Initialize daily data
        $currentDate = new \DateTime($dateRange['start']);
        $endDate = new \DateTime($dateRange['end']);
        
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $analytics['daily'][$dateStr] = [
                'date' => $dateStr,
                'impressions' => 0,
                'clicks' => 0,
                'ctr' => 0
            ];
            $currentDate->modify('+1 day');
        }
        
        // Fill in actual data
        foreach ($results as $row) {
            $date = $row['date'];
            $eventType = $row['event_type'];
            $count = (int)$row['count'];
            
            if ($eventType === 'impression') {
                $analytics['daily'][$date]['impressions'] = $count;
                $analytics['totals']['impressions'] += $count;
            } elseif ($eventType === 'content_click') {
                $analytics['daily'][$date]['clicks'] = $count;
                $analytics['totals']['clicks'] += $count;
            }
        }
        
        // Calculate CTR for each day and overall
        foreach ($analytics['daily'] as &$day) {
            $day['ctr'] = $day['impressions'] > 0 
                ? round(($day['clicks'] / $day['impressions']) * 100, 2) 
                : 0;
        }
        
        $analytics['totals']['ctr'] = $analytics['totals']['impressions'] > 0 
            ? round(($analytics['totals']['clicks'] / $analytics['totals']['impressions']) * 100, 2) 
            : 0;
        
        return $analytics;
    }
    
    /**
     * Get content effectiveness metrics
     *
     * @param int|null $contentId Specific content ID or null for all
     * @param string $timeframe Timeframe to analyze
     * @return array Content effectiveness metrics
     */
    public static function getContentEffectiveness(?int $contentId = null, string $timeframe = 'month'): array {
        $dateRange = self::getDateRangeForTimeframe($timeframe);
        
        $conditions = ["created_at BETWEEN ? AND ?"];
        $params = [$dateRange['start'], $dateRange['end']];
        
        if ($contentId) {
            $conditions[] = "JSON_EXTRACT(data, '$.content_id') = ?";
            $params[] = $contentId;
        }
        
        $whereClause = implode(' AND ', $conditions);
        
        // Get metrics for each content item
        $query = "
            SELECT 
                JSON_EXTRACT(data, '$.content_id') as content_id,
                event_type,
                COUNT(*) as count
            FROM 
                analytics_events
            WHERE 
                $whereClause
                AND event_type IN ('impression', 'content_click', 'time_spent')
                AND JSON_EXTRACT(data, '$.content_id') IS NOT NULL
            GROUP BY 
                JSON_EXTRACT(data, '$.content_id'), event_type
        ";
        
        $results = DatabaseConnection::fetchAll($query, $params);
        
        // Process results
        $contentMetrics = [];
        
        foreach ($results as $row) {
            $id = json_decode($row['content_id']);
            $eventType = $row['event_type'];
            $count = (int)$row['count'];
            
            if (!isset($contentMetrics[$id])) {
                $contentMetrics[$id] = [
                    'content_id' => $id,
                    'impressions' => 0,
                    'clicks' => 0,
                    'time_spent' => 0,
                    'ctr' => 0,
                    'avg_time' => 0
                ];
            }
            
            if ($eventType === 'impression') {
                $contentMetrics[$id]['impressions'] = $count;
            } elseif ($eventType === 'content_click') {
                $contentMetrics[$id]['clicks'] = $count;
            } elseif ($eventType === 'time_spent') {
                // For time_spent, we need to get the average duration
                $timeQuery = "
                    SELECT 
                        AVG(JSON_EXTRACT(data, '$.duration')) as avg_duration
                    FROM 
                        analytics_events
                    WHERE 
                        $whereClause
                        AND event_type = 'time_spent'
                        AND JSON_EXTRACT(data, '$.content_id') = ?
                ";
                
                $timeParams = $params;
                $timeParams[] = $id;
                
                $timeResult = DatabaseConnection::fetchOne($timeQuery, $timeParams);
                $contentMetrics[$id]['time_spent'] = $count;
                $contentMetrics[$id]['avg_time'] = $timeResult ? round($timeResult['avg_duration'], 2) : 0;
            }
        }
        
        // Calculate CTR
        foreach ($contentMetrics as &$metrics) {
            $metrics['ctr'] = $metrics['impressions'] > 0 
                ? round(($metrics['clicks'] / $metrics['impressions']) * 100, 2) 
                : 0;
        }
        
        // Get content details
        if (!empty($contentMetrics)) {
            $contentIds = array_keys($contentMetrics);
            $placeholders = implode(',', array_fill(0, count($contentIds), '?'));
            
            $contentQuery = "
                SELECT 
                    id, title, type
                FROM 
                    contents
                WHERE 
                    id IN ($placeholders)
            ";
            
            $contentResults = DatabaseConnection::fetchAll($contentQuery, $contentIds);
            
            foreach ($contentResults as $content) {
                $id = $content['id'];
                if (isset($contentMetrics[$id])) {
                    $contentMetrics[$id]['title'] = $content['title'];
                    $contentMetrics[$id]['type'] = $content['type'];
                }
            }
        }
        
        return array_values($contentMetrics);
    }
    
    /**
     * Get user engagement metrics
     *
     * @param int|null $userId Specific user ID or null for all
     * @param string $timeframe Timeframe to analyze
     * @return array User engagement metrics
     */
    public static function getUserEngagement(?int $userId = null, string $timeframe = 'month'): array {
        $dateRange = self::getDateRangeForTimeframe($timeframe);
        
        $conditions = ["created_at BETWEEN ? AND ?"];
        $params = [$dateRange['start'], $dateRange['end']];
        
        if ($userId) {
            $conditions[] = "user_id = ?";
            $params[] = $userId;
        }
        
        $whereClause = implode(' AND ', $conditions);
        
        // Get user engagement metrics
        $query = "
            SELECT 
                user_id,
                COUNT(DISTINCT DATE(created_at)) as active_days,
                COUNT(*) as total_events,
                COUNT(DISTINCT session_id) as sessions
            FROM 
                analytics_events
            WHERE 
                $whereClause
                AND user_id > 0
            GROUP BY 
                user_id
            ORDER BY 
                total_events DESC
        ";
        
        $results = DatabaseConnection::fetchAll($query, $params);
        
        // Get event type breakdown for each user
        foreach ($results as &$user) {
            $userId = $user['user_id'];
            
            $eventQuery = "
                SELECT 
                    event_type,
                    COUNT(*) as count
                FROM 
                    analytics_events
                WHERE 
                    user_id = ?
                    AND created_at BETWEEN ? AND ?
                GROUP BY 
                    event_type
            ";
            
            $eventParams = [$userId, $dateRange['start'], $dateRange['end']];
            $eventResults = DatabaseConnection::fetchAll($eventQuery, $eventParams);
            
            $events = [];
            foreach ($eventResults as $event) {
                $events[$event['event_type']] = (int)$event['count'];
            }
            
            $user['events'] = $events;
            
            // Calculate engagement score (simple algorithm)
            $user['engagement_score'] = self::calculateEngagementScore($user);
        }
        
        return $results;
    }
    
    /**
     * Calculate engagement score for a user
     *
     * @param array $userData User data with event counts
     * @return float Engagement score (0-100)
     */
    private static function calculateEngagementScore(array $userData): float {
        // Simple scoring algorithm
        $score = 0;
        
        // Active days (up to 30 points)
        $score += min($userData['active_days'] * 3, 30);
        
        // Sessions (up to 20 points)
        $score += min($userData['sessions'], 20);
        
        // Content clicks (up to 25 points)
        $score += min(($userData['events']['content_click'] ?? 0) / 2, 25);
        
        // Time spent (up to 15 points)
        $score += min(($userData['events']['time_spent'] ?? 0) / 3, 15);
        
        // Conversions (up to 10 points)
        $score += min(($userData['events']['conversion'] ?? 0) * 2, 10);
        
        return round($score, 1);
    }
    
    /**
     * Get date range for a timeframe
     *
     * @param string $timeframe Timeframe (day, week, month, year)
     * @return array Start and end dates
     */
    private static function getDateRangeForTimeframe(string $timeframe): array {
        $end = date('Y-m-d 23:59:59');
        
        switch ($timeframe) {
            case 'day':
                $start = date('Y-m-d 00:00:00');
                break;
            case 'week':
                $start = date('Y-m-d 00:00:00', strtotime('-7 days'));
                break;
            case 'month':
                $start = date('Y-m-d 00:00:00', strtotime('-30 days'));
                break;
            case 'year':
                $start = date('Y-m-d 00:00:00', strtotime('-365 days'));
                break;
            default:
                $start = date('Y-m-d 00:00:00', strtotime('-30 days'));
        }
        
        return [
            'start' => $start,
            'end' => $end
        ];
    }
    
    /**
     * Export analytics data to CSV
     *
     * @param string $eventType Event type to export
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @return string CSV content
     */
    public static function exportToCsv(string $eventType, string $startDate, string $endDate): string {
        $query = "
            SELECT 
                id,
                event_type,
                user_id,
                session_id,
                data,
                created_at
            FROM 
                analytics_events
            WHERE 
                event_type = ?
                AND created_at BETWEEN ? AND ?
            ORDER BY 
                created_at DESC
        ";
        
        $results = DatabaseConnection::fetchAll($query, [
            $eventType,
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59'
        ]);
        
        // Generate CSV
        $output = fopen('php://temp', 'r+');
        
        // Write header
        fputcsv($output, ['ID', 'Event Type', 'User ID', 'Session ID', 'Data', 'Created At']);
        
        // Write data
        foreach ($results as $row) {
            fputcsv($output, [
                $row['id'],
                $row['event_type'],
                $row['user_id'],
                $row['session_id'],
                $row['data'],
                $row['created_at']
            ]);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
}
