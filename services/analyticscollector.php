<?php

require_once __DIR__ . '/../core/database.php';

class AnalyticsCollector {
    private static ?AnalyticsCollector $instance = null;
    private $db;

    private function __construct() {
        $this->db = \core\Database::connection();
    }

    public static function getInstance(): AnalyticsCollector {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function logEvent(string $tenantId, string $eventType, array $eventData = [], ?string $userId = null): bool {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO tenant_analytics_events 
                (tenant_id, event_type, event_data, user_id, ip_address, user_agent)
                VALUES (:tenant_id, :event_type, :event_data, :user_id, :ip_address, :user_agent)
            ");

            return $stmt->execute([
                ':tenant_id' => $tenantId,
                ':event_type' => $eventType,
                ':event_data' => json_encode($eventData),
                ':user_id' => $userId,
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("AnalyticsCollector error: " . $e->getMessage());
            return false;
        }
    }

    public function getEvents(string $tenantId, int $limit = 100): array {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM tenant_analytics_events 
                WHERE tenant_id = :tenant_id
                ORDER BY timestamp DESC
                LIMIT :limit
            ");
            $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("AnalyticsCollector error: " . $e->getMessage());
            return [];
        }
    }
}
