<?php

class ContentUpdateTrigger implements WorkflowTrigger {
    public function execute(array $payload): void {
        // Log the trigger execution with update-specific details
        $logEntry = [
            'action' => 'content_update',
            'content_id' => $payload['id'] ?? null,
            'changed_fields' => $payload['changed'] ?? [],
            'timestamp' => time()
        ];
        
        file_put_contents(
            __DIR__ . '/../../../logs/progress.md',
            "## [".date('Y-m-d')."] ContentUpdateTrigger\n- Updated content ID: ".($payload['id'] ?? 'unknown')."\n- Changed fields: ".implode(', ', $payload['changed'] ?? [])."\n",
            FILE_APPEND
        );
    }
}
