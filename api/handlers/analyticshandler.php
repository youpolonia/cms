<?php
namespace Api\Handlers;

use PDO;
use Models\AnalyticsEvent;

class AnalyticsHandler
{
    private ?PDO $db;

    public function __construct(?PDO $db)
    {
        $this->db = $db;
    }

    private function requireDb(): void
    {
        if ($this->db === null) {
            http_response_code(500);
            echo json_encode(['error' => 'Database connection not available for AnalyticsHandler.']);
            exit;
        }
    }

    /**
     * Records an analytics event
     * POST /api.php/analytics/event
     */
    public function postEvent(?string $id, array $requestData): void
    {
        $this->requireDb();
        
        // Validate required fields
        $required = ['event_type', 'tenant_id', 'user_id', 'content_id'];
        foreach ($required as $field) {
            if (empty($requestData[$field])) {
                $this->jsonResponse(['error' => "Missing required field: $field"], 400);
                return;
            }
        }

        // Record event
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO tenant_analytics_events 
                (event_type, tenant_id, user_id, content_id, event_data, created_at)
                VALUES (:event_type, :tenant_id, :user_id, :content_id, :event_data, NOW())"
            );
            
            $stmt->execute([
                ':event_type' => $requestData['event_type'],
                ':tenant_id' => $requestData['tenant_id'],
                ':user_id' => $requestData['user_id'],
                ':content_id' => $requestData['content_id'],
                ':event_data' => json_encode($requestData['event_data'] ?? [])
            ]);

            $this->jsonResponse(['success' => true, 'event_id' => $this->db->lastInsertId()]);
        } catch (\PDOException $e) {
            $this->jsonResponse(['error' => 'Failed to record event: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Gets analytics summary
     * GET /api.php/analytics/summary
     */
    public function getSummary(?string $id, array $requestData): void
    {
        $this->requireDb();
        
        // Validate required fields
        if (empty($requestData['tenant_id'])) {
            $this->jsonResponse(['error' => 'Missing required field: tenant_id'], 400);
            return;
        }

        try {
            // Get counts by event type
            $stmt = $this->db->prepare(
                "SELECT event_type, COUNT(*) as count 
                 FROM tenant_analytics_events
                 WHERE tenant_id = :tenant_id
                 GROUP BY event_type"
            );
            $stmt->execute([':tenant_id' => $requestData['tenant_id']]);
            $counts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get recent activity
            $stmt = $this->db->prepare(
                "SELECT event_type, content_id, created_at
                 FROM tenant_analytics_events
                 WHERE tenant_id = :tenant_id
                 ORDER BY created_at DESC
                 LIMIT 10"
            );
            $stmt->execute([':tenant_id' => $requestData['tenant_id']]);
            $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonResponse([
                'counts_by_type' => $counts,
                'recent_activity' => $recent
            ]);
        } catch (\PDOException $e) {
            $this->jsonResponse(['error' => 'Failed to get summary: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Gets analytics events
     * GET /api.php/analytics/events
     */
    public function getEvents(?string $id, array $requestData): void
    {
        $this->requireDb();
        
        // Validate required fields
        if (empty($requestData['tenant_id'])) {
            $this->jsonResponse(['error' => 'Missing required field: tenant_id'], 400);
            return;
        }

        try {
            $limit = min($requestData['limit'] ?? 100, 1000);
            $offset = $requestData['offset'] ?? 0;

            $stmt = $this->db->prepare(
                "SELECT * FROM tenant_analytics_events
                 WHERE tenant_id = :tenant_id
                 ORDER BY created_at DESC
                 LIMIT :limit OFFSET :offset"
            );
            $stmt->bindParam(':tenant_id', $requestData['tenant_id'], PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->jsonResponse($events);
        } catch (\PDOException $e) {
            $this->jsonResponse(['error' => 'Failed to get events: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper for JSON response within this handler.
     */
    private function jsonResponse(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}
