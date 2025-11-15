<?php

class SEOModificationTrigger implements WorkflowTrigger {
    public function execute(array $payload): void {
        // Log SEO field modifications
        $logEntry = [
            'action' => 'seo_modification',
            'content_id' => $payload['id'] ?? null,
            'modified_fields' => $payload['seo_fields'] ?? [],
            'timestamp' => time()
        ];
        
        file_put_contents(
            __DIR__ . '/../../../logs/progress.md',
            "## [".date('Y-m-d')."] SEOModificationTrigger\n- Content ID: ".($payload['id'] ?? 'unknown')."\n- Modified SEO fields: ".implode(', ', array_keys($payload['seo_fields'] ?? []))."\n",
            FILE_APPEND
        );
    }
}
