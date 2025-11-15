<?php

class ContentDeleteTrigger implements WorkflowTrigger {
    public function execute(array $payload): void {
        // Log the deletion event with relevant details
        $logEntry = [
            'action' => 'content_delete',
            'content_id' => $payload['id'] ?? null,
            'deleted_at' => time(),
            'deleted_by' => $payload['user_id'] ?? null
        ];
        
        file_put_contents(
            __DIR__ . '/../../../logs/progress.md',
            "## [".date('Y-m-d')."] ContentDeleteTrigger\n- Deleted content ID: ".($payload['id'] ?? 'unknown')."\n- Deleted by user: ".($payload['user_id'] ?? 'system')."\n",
            FILE_APPEND
        );
    }
}
