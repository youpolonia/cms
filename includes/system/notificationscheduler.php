<?php

require_once __DIR__ . '/../../core/database.php';

class NotificationScheduler {
    public static function run(): void {
        $service = NotificationService::getInstance();
        $service->processQueue();

        $handler = new OptimizedNotificationHandler(
            \core\Database::connection(),
            Mailer::getInstance(),
            WebhookService::getInstance()
        );
        $handler->processBatch();
    }
}
