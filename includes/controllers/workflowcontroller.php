<?php
require_once __DIR__ . '/../../core/loggerfactory.php';
require_once __DIR__ . '/../services/workflowengine.php';

class WorkflowController {
    private $workflowEngine;
    private $logger;
    
    public function __construct($db) {
        $this->workflowEngine = new WorkflowEngine($db);
        $this->logger = LoggerFactory::create('file', [
            'file_path' => __DIR__ . '/../../logs/workflow.log',
            'level' => 'debug'
        ]);
    }
    
    /**
     * Process workflow queue item with PSR-3 compliant logging
     */
    public function processQueueItem($queueId) {
        try {
            $this->logger->info("Processing queue item {$queueId}");
            $result = $this->workflowEngine->processQueueItem($queueId);
            
            if ($result) {
                $this->logger->info("Successfully processed queue item {$queueId}");
            } else {
                $this->logger->warning("Failed to process queue item {$queueId}");
            }
            
            return $result;
        } catch (Exception $e) {
            $this->logger->error("Error processing queue item {$queueId}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Backward compatible logging method
     */
    public function logLegacy($message, $level = 'info') {
        switch (strtolower($level)) {
            case 'error':
                $this->logger->error($message);
                break;
            case 'warning':
                $this->logger->warning($message);
                break;
            default:
                $this->logger->info($message);
        }
    }
    
    /**
     * Get moderation service from workflow engine
     */
    public function getModerationService() {
        return $this->workflowEngine->getModerationService();
    }
}
