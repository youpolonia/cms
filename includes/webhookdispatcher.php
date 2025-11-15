<?php
/**
 * Webhook Dispatcher for n8n Integration
 *
 * Routes incoming webhook requests to appropriate handlers
 */

class WebhookDispatcher {
    private const LOG_FILE = 'storage/logs/webhooks.log';
    private const HANDLERS = [
        'content.publish' => 'handleContentPublish',
        'backup.initiate' => 'handleBackupInitiate', 
        'seo.update' => 'handleSeoUpdate'
    ];

    /**
     * Handle incoming webhook payload
     */
    public function handle(array $payload): array {
        if (empty($payload['event'])) {
            throw new InvalidArgumentException('Missing event type in payload');
        }

        $event = $payload['event'];
        if (!isset(self::HANDLERS[$event])) {
            throw new InvalidArgumentException("Unsupported event type: {$event}");
        }

        $handlerMethod = self::HANDLERS[$event];
        return $this->$handlerMethod($payload);
    }

    /**
     * Handle content publishing event
     */
    private function onContentPublish(array $payload): array {
        // AI Content Validation
        $content = ContentVersionController::getById($payload['content_id'], $payload['version']);
        $aiCheck = AIContentValidator::validate($content);
        if (!$aiCheck['valid']) {
            throw new ContentValidationException('AI validation failed: ' . $aiCheck['reason']);
        }

        // Validate required fields
        $required = ['content_id', 'version', 'published_by'];
        $this->validatePayload($payload, $required);

        // Log the event
        $this->logEvent('content.publish', [
            'content_id' => $payload['content_id'],
            'version' => $payload['version'],
            'published_by' => $payload['published_by']
        ]);

        // Trigger content publishing logic
        // TODO: Implement actual content publishing integration
        return [
            'status' => 'success',
            'message' => 'Content publishing triggered',
            'content_id' => $payload['content_id']
        ];
    }

    /**
     * Handle backup initiation event
     */
    private function handleBackupInitiate(array $payload): array {
        // Validate required fields
        $this->validatePayload($payload, ['backup_type', 'initiated_by']);

        // Log the event
        $this->logEvent('backup.initiate', [
            'backup_type' => $payload['backup_type'],
            'initiated_by' => $payload['initiated_by']
        ]);

        // Trigger backup process
        // TODO: Implement actual backup integration
        return [
            'status' => 'success',
            'message' => 'Backup process initiated',
            'backup_id' => uniqid('backup_')
        ];
    }

    /**
     * Log webhook events to file
     */
    private function logEvent(string $event, array $data): void {
        $logDir = dirname(self::LOG_FILE);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logEntry = sprintf(
            "[%s] %s: %s\n",
            date('Y-m-d H:i:s'),
            $event,
            json_encode($data)
        );

        file_put_contents(self::LOG_FILE, $logEntry, FILE_APPEND);
    }

    /**
     * Handle SEO update event
     */
    private function handleSeoUpdate(array $payload): array {
        // Validate required fields
        $required = ['entity_type', 'entity_id', 'seo_fields'];
        $this->validatePayload($payload, $required);

        // Process SEO updates
        // TODO: Implement actual SEO integration
        return [
            'status' => 'success',
            'message' => 'SEO updates processed',
            'entity' => "{$payload['entity_type']}:{$payload['entity_id']}"
        ];
    }

    /**
     * Validate payload contains required fields
     */
    private function validatePayload(array $payload, array $requiredFields): void {
        foreach ($requiredFields as $field) {
            if (!isset($payload[$field])) {
                throw new InvalidArgumentException("Missing required field: {$field}");
            }
        }
    }
}
