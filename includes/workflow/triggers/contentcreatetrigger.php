<?php

class ContentCreateTrigger implements WorkflowTrigger {
    public function execute(array $payload): void {
        // Log the trigger execution
        $logEntry = [
            'action' => 'content_create',
            'content_id' => $payload['id'] ?? null,
            'timestamp' => time()
        ];
        
        // In practice, this would trigger actual workflows
        // For now just log to memory-bank/progress.md
        file_put_contents(
            __DIR__ . '/../../../logs/progress.md',
            "## [".date('Y-m-d')."] ContentCreateTrigger\n- Fired for content ID: ".($payload['id'] ?? 'unknown')."\n",
            FILE_APPEND
        );
    }
}
