<?php
/**
 * Batch Processor Service
 * Handles batch processing of scheduled content publishing jobs
 */
class BatchProcessor {
    /**
     * @var array $currentBatch Current batch being processed
     */
    private $currentBatch = [];

    /**
     * @var int $batchSize Maximum number of items per batch
     */
    private $batchSize = 100;

    /**
     * @var PDO $db Database connection
     */
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Process a batch of scheduled events
     *
     * @param array $eventIds Array of scheduled event IDs to process
     * @return array Processing results
     */
    public function processBatch(array $eventIds): array {
        $results = [];
        $this->currentBatch = array_slice($eventIds, 0, $this->batchSize);

        foreach ($this->currentBatch as $eventId) {
            try {
                $results[$eventId] = $this->processEvent($eventId);
            } catch (Exception $e) {
                $this->markEventFailed($eventId, $e->getMessage());
                $results[$eventId] = [
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Process a single scheduled event
     *
     * @param int $eventId Scheduled event ID to process
     * @return array Processing result
     */
    private function processEvent(int $eventId): array {
        // Get event details with content version
        $stmt = $this->db->prepare(
            "SELECT se.*, cv.content_data
             FROM scheduled_events se
             JOIN content_versions cv ON se.version_id = cv.id
             WHERE se.id = ?"
        );
        $stmt->execute([$eventId]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            throw new Exception("Event not found");
        }

        try {
            // Begin transaction
            $this->db->beginTransaction();

            // 1. Update the content record
            $updateStmt = $this->db->prepare(
                "UPDATE contents
                 SET content_data = ?, version_id = ?, updated_at = NOW()
                 WHERE id = ?"
            );
            $updateStmt->execute([
                $event['content_data'],
                $event['version_id'],
                $event['content_id']
            ]);

            // 2. Mark event as published
            $this->markEventPublished($eventId);

            // 3. Log the publication
            $logStmt = $this->db->prepare(
                "INSERT INTO content_publication_log
                 (content_id, version_id, published_at, published_by)
                 VALUES (?, ?, NOW(), ?)"
            );
            $logStmt->execute([
                $event['content_id'],
                $event['version_id'],
                $_SESSION['user_id'] ?? null
            ]);

            $this->db->commit();

            return [
                'status' => 'published',
                'event_id' => $eventId,
                'content_id' => $event['content_id'],
                'version_id' => $event['version_id'],
                'processed_at' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->markEventFailed($eventId, $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mark event as published in database
     */
    private function markEventPublished(int $eventId): void {
        $stmt = $this->db->prepare(
            "UPDATE scheduled_events
             SET status = 'published',
                 processed_at = NOW(),
                 error_message = NULL
             WHERE id = ?"
        );
        $stmt->execute([$eventId]);
    }

    /**
     * Mark event as failed in database
     */
    private function markEventFailed(int $eventId, string $error): void {
        $stmt = $this->db->prepare(
            "UPDATE scheduled_events
             SET status = 'failed',
                 processed_at = NOW(),
                 error_message = ?
             WHERE id = ?"
        );
        $stmt->execute([$error, $eventId]);
    }

    /**
     * Get current batch size limit
     */
    public function getBatchSize(): int {
        return $this->batchSize;
    }

    /**
     * Set batch size limit
     */
    public function setBatchSize(int $size): void {
        $this->batchSize = max(1, min(1000, $size));
    }
}
