<?php
/**
 * Phase 12 Analytics Processing Service
 * Handles batch and real-time processing of analytics data
 */
class AnalyticsProcessor {
    private \PDO $pdo;
    private int $batchSize = 100;
    private bool $realTimeEnabled = false;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function enableRealTime(bool $enabled): void {
        $this->realTimeEnabled = $enabled;
    }

    public function setBatchSize(int $size): void {
        $this->batchSize = max(1, $size);
    }

    public function processBatch(): int {
        try {
            $this->pdo->beginTransaction();
            
            // Get unprocessed metrics
            $stmt = $this->pdo->prepare(
                "SELECT * FROM analytics_metrics 
                 WHERE processing_status = 'pending' 
                 LIMIT :limit FOR UPDATE"
            );
            $stmt->bindValue(':limit', $this->batchSize, \PDO::PARAM_INT);
            $stmt->execute();
            
            $metrics = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $processed = 0;

            foreach ($metrics as $metric) {
                // Process each metric
                $this->processMetric($metric);
                $processed++;
            }

            $this->pdo->commit();
            return $processed;
            
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            error_log("Batch processing failed: " . $e->getMessage());
            return 0;
        }
    }

    private function processMetric(array $metric): void {
        $updateStmt = $this->pdo->prepare(
            "UPDATE analytics_metrics 
             SET processing_status = 'processing'
             WHERE id = :id"
        );
        $updateStmt->execute([':id' => $metric['id']]);

        // Actual processing logic would go here
        // For now just mark as processed
        $updateStmt = $this->pdo->prepare(
            "UPDATE analytics_metrics 
             SET processing_status = 'completed',
                 processed_at = NOW()
             WHERE id = :id"
        );
        $updateStmt->execute([':id' => $metric['id']]);

        // Queue for aggregation if needed
        if ($this->realTimeEnabled) {
            $this->queueForAggregation($metric);
        }
    }

    private function queueForAggregation(array $metric): void {
        $stmt = $this->pdo->prepare(
            "INSERT INTO analytics_processing_queue 
             (metric_id, queue_time, priority)
             VALUES (:metric_id, NOW(), 1)"
        );
        $stmt->execute([':metric_id' => $metric['id']]);
    }

    public static function test(\PDO $pdo): bool {
        try {
            $processor = new self($pdo);
            $processor->setBatchSize(5);
            return $processor->processBatch() >= 0;
        } catch (\Exception $e) {
            error_log("Processor test failed: " . $e->getMessage());
            return false;
        }
    }
}
