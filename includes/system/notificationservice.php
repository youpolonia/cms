<?php

require_once __DIR__ . '/../../core/database.php';

class NotificationService {
    private static $instance;
    private $notificationManager;
    private $handler;
    private $scheduler;

    private function __construct() {
        $this->notificationManager = new NotificationManager();
        $this->handler = new OptimizedNotificationHandler(
            \core\Database::connection(),
            Mailer::getInstance(),
            WebhookService::getInstance()
        );
    }

    public static function getInstance(): self {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function registerScheduler(Scheduler $scheduler): void {
        $this->scheduler = $scheduler;
        $this->scheduler->addJob('process_notifications', fn() => $this->processQueue());
    }

    public function processQueue(): void {
        $notifications = $this->notificationManager->getQueuedNotifications();
        
        foreach ($notifications as $notification) {
            $this->handler->sendNotification(
                $notification['user_id'] ?? 0,
                $notification['schedule_id'] ?? null,
                $notification['type'],
                $notification['context'],
                $notification['message']
            );
            
            $this->notificationManager->clearNotification($notification['id']);
        }
    }

    public function queueNotification(
        string $type, 
        string $message, 
        array $context = [], 
        ?int $userId = null
    ): bool {
        return $this->notificationManager->queueNotification(
            $type,
            $message,
            array_merge($context, ['user_id' => $userId])
        );
    }
}
