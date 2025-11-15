<?php
/**
 * Batch Processor Worker
 * Handles processing of batch jobs with status tracking and error handling
 */
class BatchProcessor
{
    private $db;
    private $batchId;
    private $batchType;
    private $filePath;
    private $processedItems = 0;
    private $totalItems = 0;
    private $versionedScheduleService;
    private $conditionEvaluator;
    private $contentLifecycleManager;

    public function __construct(int $batchId) {
        $this->db = Connection::getInstance();
        $this->batchId = $batchId;
        
        // Initialize services
        $this->versionedScheduleService = new VersionedScheduleService();
        $this->conditionEvaluator = new ConditionEvaluator();
        $this->contentLifecycleManager = new ContentLifecycleManager();
    }

    /**
     * Process the batch job
     */
    public function process(): void {
        try {
            $this->updateStatus('initializing');
            $this->loadBatchData();
            
            $this->updateStatus('processing');
            $this->processBatchItems();
            
            $this->updateStatus('completed');
            $this->logCompletion();
        } catch (Exception $e) {
            $this->updateStatus('failed');
            $this->logError('Batch processing failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function loadBatchData(): void {
        // Load batch metadata
        $stmt = $this->db->prepare("SELECT * FROM batch_metadata WHERE batch_id = ?");
        $stmt->execute([$this->batchId]);
        $metadata = $stmt->fetch();

        if (!$metadata) {
            throw new Exception("Batch metadata not found");
        }

        $this->batchType = $metadata['batch_type'];
        
        // Get total items count
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM batch_items WHERE batch_id = ?");
        $stmt->execute([$this->batchId]);
        $this->totalItems = $stmt->fetchColumn();
    }

    private function processBatchItems(): void {
        $offset = 0;
        $limit = 100; // Process in chunks
        
        while ($items = $this->getBatchItems($offset, $limit)) {
            foreach ($items as $item) {
                try {
                    $this->processItem($item);
                    $this->processedItems++;
                    $this->updateProgress();
                } catch (Exception $e) {
                    $this->logError("Failed processing item {$item['id']}: " . $e->getMessage());
                }
            }
            $offset += $limit;
        }
    }

    private function getBatchItems(int $offset, int $limit): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM batch_items 
             WHERE batch_id = ? 
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$this->batchId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    private function processItem(array $item): void {
        switch ($this->batchType) {
            case 'content_scheduling':
                $this->processScheduledItem($item);
                break;
            case 'content_import':
                $this->processImportItem($item);
                break;
            default:
                throw new Exception("Unknown batch type: {$this->batchType}");
        }
    }

    private function processScheduledItem(array $item): void {
        // Evaluate conditions first
        if (!$this->conditionEvaluator->evaluate($item['conditions'])) {
            $this->logError("Item {$item['id']} conditions not met");
            return;
        }

        // Validate status transition
        if (!$this->contentLifecycleManager->validateTransition($item['content_id'], 'scheduled')) {
            $this->logError("Item {$item['id']} invalid status transition");
            return;
        }

        // Process with versioned schedule service
        $this->db->beginTransaction();
        try {
            $this->versionedScheduleService->schedule(
                $item['content_id'],
                $item['version_id'],
                $item['scheduled_at']
            );
            
            // Update status if successful
            $this->contentLifecycleManager->updateStatusAutomatically($item['content_id']);
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function processImportItem(array $item): void {
        // Implementation would depend on specific import logic
        // Placeholder for actual import processing
    }

    private function updateStatus(string $status): void {
        $stmt = $this->db->prepare(
            "UPDATE batch_metadata SET status = ?, updated_at = NOW() 
             WHERE batch_id = ?"
        );
        $stmt->execute([$status, $this->batchId]);
    }

    private function updateProgress(): void {
        if ($this->totalItems > 0) {
            $progress = round(($this->processedItems / $this->totalItems) * 100);
            $stmt = $this->db->prepare(
                "UPDATE batch_metadata SET progress = ? 
                 WHERE batch_id = ?"
            );
            $stmt->execute([$progress, $this->batchId]);
        }
    }

    private function logCompletion(): void {
        $stmt = $this->db->prepare(
            "UPDATE batch_metadata 
             SET completed_at = NOW(), 
                 processing_time = TIMESTAMPDIFF(SECOND, created_at, NOW())
             WHERE batch_id = ?"
        );
        $stmt->execute([$this->batchId]);
    }

    private function logError(string $message): void {
        $stmt = $this->db->prepare(
            "UPDATE batch_metadata 
             SET error_log = CONCAT(COALESCE(error_log, ''), ?) 
             WHERE batch_id = ?"
        );
        $stmt->execute([date('Y-m-d H:i:s') . ' - ' . $message . "\n", $this->batchId]);
    }
}
