<?php
/**
 * Audit Logger for Workflow System
 * Tracks and records all workflow events and state changes
 */
class AuditLogger {
    private static $instance;
    private $storage = [];
    private $maxEntries = 1000;

    private function __construct() {
        // Initialize with empty storage
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function logEvent($eventType, $entityId, $details = []) {
        $entry = [
            'timestamp' => time(),
            'event_type' => $eventType,
            'entity_id' => $entityId,
            'details' => $details,
            'user_id' => $_SESSION['user_id'] ?? null
        ];

        array_unshift($this->storage, $entry);

        // Maintain max entries limit
        if (count($this->storage) > $this->maxEntries) {
            array_pop($this->storage);
        }

        // Notify about the audit event
        NotificationService::getInstance()->sendNotification('audit', [
            'type' => $eventType,
            'id' => $entityId
        ]);
    }

    public function getEvents($filter = []) {
        $results = $this->storage;

        if (!empty($filter['type'])) {
            $results = array_filter($results, function($entry) use ($filter) {
                return $entry['event_type'] === $filter['type'];
            });
        }

        if (!empty($filter['entity_id'])) {
            $results = array_filter($results, function($entry) use ($filter) {
                return $entry['entity_id'] === $filter['entity_id'];
            });
        }

        return array_values($results);
    }

    public function getRecentEvents($limit = 10) {
        return array_slice($this->storage, 0, $limit);
    }
}
