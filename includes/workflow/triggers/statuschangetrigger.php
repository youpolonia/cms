<?php

class StatusChangeTrigger implements WorkflowTrigger {
    public function execute(array $payload): void {
        // Log status transition with before/after states
        $logEntry = [
            'action' => 'status_change',
            'content_id' => $payload['id'] ?? null,
            'from_status' => $payload['from'] ?? null,
            'to_status' => $payload['to'] ?? null,
            'timestamp' => time()
        ];
        
        file_put_contents(
            __DIR__ . '/../../../logs/progress.md',
            "## [".date('Y-m-d')."] StatusChangeTrigger\n- Content ID: ".($payload['id'] ?? 'unknown')."\n- Status changed from '".($payload['from'] ?? 'unknown')."' to '".($payload['to'] ?? 'unknown')."'\n",
            FILE_APPEND
        );
    }
}
