<?php
/**
 * API v1 Analytics Controller
 */

declare(strict_types=1);

namespace Api\V1\Controllers;

use Includes\Routing\Request;
use Includes\Routing\Response;
use Includes\Database\Connection;

class AnalyticsController
{
    /**
     * Track analytics event for a version
     */
    public function trackEvent(Request $request, Response $response): void
    {
        $versionId = (int)$request->getParam('id');
        $data = $request->getParsedBody();
        
        // Validate required fields
        if (empty($data['event_type'])) {
            $response->json([
                'success' => false,
                'data' => null,
                'error' => 'Missing required field: event_type'
            ], 400);
            return;
        }

        $db = \core\Database::connection();
        
        try {
            $stmt = $db->prepare("
                INSERT INTO version_analytics 
                (version_id, event_type, event_data, user_agent, ip_address, created_at) 
                VALUES 
                (:version_id, :event_type, :event_data, :user_agent, :ip_address, NOW())
            ");
            
            $stmt->execute([
                ':version_id' => $versionId,
                ':event_type' => $data['event_type'],
                ':event_data' => json_encode($data['event_data'] ?? []),
                ':user_agent' => $request->getHeader('User-Agent')[0] ?? null,
                ':ip_address' => $request->getAttribute('ip_address')
            ]);

            $response->json([
                'success' => true,
                'data' => ['event_id' => $db->lastInsertId()],
                'error' => null
            ], 201);
        } catch (\Exception $e) {
            $response->json([
                'success' => false,
                'data' => null,
                'error' => 'Failed to track event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get analytics for a version
     */
    public function getAnalytics(Request $request, Response $response): void
    {
        $versionId = (int)$request->getParam('id');
        $queryParams = $request->getQueryParams();
        
        // Default date range: last 30 days
        $startDate = $queryParams['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $queryParams['end_date'] ?? date('Y-m-d');

        $db = \core\Database::connection();
        
        try {
            // Get event type counts
            $stmt = $db->prepare("
                SELECT event_type, COUNT(*) as count 
                FROM version_analytics 
                WHERE version_id = :version_id 
                AND created_at BETWEEN :start_date AND :end_date
                GROUP BY event_type
            ");
            
            $stmt->execute([
                ':version_id' => $versionId,
                ':start_date' => $startDate,
                ':end_date' => $endDate
            ]);
            
            $eventCounts = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get timeline data
            $stmt = $db->prepare("
                SELECT 
                    DATE(created_at) as date,
                    event_type,
                    COUNT(*) as count
                FROM version_analytics
                WHERE version_id = :version_id
                AND created_at BETWEEN :start_date AND :end_date
                GROUP BY DATE(created_at), event_type
                ORDER BY DATE(created_at)
            ");
            
            $stmt->execute([
                ':version_id' => $versionId,
                ':start_date' => $startDate,
                ':end_date' => $endDate
            ]);
            
            $timelineData = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $response->json([
                'success' => true,
                'data' => [
                    'event_counts' => $eventCounts,
                    'timeline_data' => $timelineData,
                    'date_range' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate
                    ]
                ],
                'error' => null
            ]);
        } catch (\Exception $e) {
            $response->json([
                'success' => false,
                'data' => null,
                'error' => 'Failed to fetch analytics: ' . $e->getMessage()
            ], 500);
        }
    }
}
