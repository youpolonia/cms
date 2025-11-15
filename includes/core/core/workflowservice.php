<?php
/**
 * Workflow Service - Core workflow management
 * 
 * @package Core
 */

declare(strict_types=1);

namespace Core;

use Logging\Logger;
use Exception;

class WorkflowService {
    private const STATE_FILE = __DIR__.'/../../storage/workflow_states.json';
    private Logger $logger;

    public function __construct(Logger $logger) {
        $this->logger = $logger;
        $this->ensureStorageDirectory();
    }

    private function ensureStorageDirectory(): void {
        $dir = dirname(self::STATE_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    public function executeWorkflow(string $workflowId, array $payload): array {
        try {
            $state = $this->loadState($workflowId);
            $newState = $this->processState($state, $payload);
            $this->saveState($workflowId, $newState);
            
            return [
                'status' => 'success',
                'state' => $newState
            ];
        } catch (Exception $e) {
            $this->logger->error("Workflow execution failed: ".$e->getMessage());
            throw $e;
        }
    }

    private function loadState(string $workflowId): array {
        if (!file_exists(self::STATE_FILE)) {
            return ['id' => $workflowId, 'status' => 'new'];
        }

        $data = json_decode(file_get_contents(self::STATE_FILE), true);
        return $data[$workflowId] ?? ['id' => $workflowId, 'status' => 'new'];
    }

    private function saveState(string $workflowId, array $state): void {
        $data = [];
        if (file_exists(self::STATE_FILE)) {
            $data = json_decode(file_get_contents(self::STATE_FILE), true);
        }
        
        $data[$workflowId] = $state;
        file_put_contents(self::STATE_FILE, json_encode($data, JSON_PRETTY_PRINT));
    }

    private function processState(array $state, array $payload): array {
        // State transition logic here
        return array_merge($state, [
            'last_updated' => date('c'),
            'payload' => $payload
        ]);
    }
}
