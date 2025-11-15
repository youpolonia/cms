<?php
declare(strict_types=1);

namespace CMS\BatchProcessing;

use CMS\ContentLifecycle\ContentLifecycleManager;
use CMS\Database\Connection;
use CMS\Exceptions\BatchProcessingException;

class BatchProcessor {
    private ContentLifecycleManager $lifecycleManager;
    private Connection $dbConnection;

    public function __construct(
        ContentLifecycleManager $manager,
        Connection $connection
    ) {
        $this->lifecycleManager = $manager;
        $this->dbConnection = $connection;
    }

    public function processBatch(array $contentIds): bool {
        if (!$this->validateContent($contentIds)) {
            throw new BatchProcessingException(
                "Invalid content status for batch processing"
            );
        }

        try {
            $this->dbConnection->beginTransaction();
            
            // Process each content item
            foreach ($contentIds as $contentId) {
                $this->processContentItem((int)$contentId);
            }

            $this->updateStatus($contentIds, 'processed');
            $this->dbConnection->commit();
            
            return true;
        } catch (\Exception $e) {
            $this->dbConnection->rollBack();
            throw new BatchProcessingException(
                "Batch processing failed: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    private function validateContent(array $contentIds): bool {
        foreach ($contentIds as $contentId) {
            if (!$this->lifecycleManager->isValidForProcessing((int)$contentId)) {
                return false;
            }
        }
        return true;
    }

    private function processContentItem(int $contentId): void {
        // Content processing logic here
    }

    private function updateStatus(array $contentIds, string $status): void {
        foreach ($contentIds as $contentId) {
            $this->lifecycleManager->updateStatus((int)$contentId, $status);
        }
    }
}
