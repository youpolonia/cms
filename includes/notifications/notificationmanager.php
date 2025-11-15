<?php
/**
 * Notification Manager
 * Handles dispatching notifications via different channels
 */
class NotificationManager {
    private $dispatchers = [];
    private $logFile;

    public function __construct(string $logFile = '') {
        $this->logFile = $logFile ?: __DIR__ . '/../../storage/logs/notifications.log';
    }

    public function addDispatcher(string $type, object $dispatcher): void {
        $this->dispatchers[$type] = $dispatcher;
    }

    public function notify(string $type, $target, string $message, array $data = []): bool {
        if (!isset($this->dispatchers[$type])) {
            $this->logError("Unsupported notification type: $type");
            return false;
        }

        try {
            switch ($type) {
                case 'email':
                    return $this->dispatchers[$type]->send(
                        $target,
                        $data['subject'] ?? 'Notification',
                        $message,
                        $data['headers'] ?? []
                    );

                case 'webhook':
                    return $this->dispatchers[$type]->send(
                        $target,
                        array_merge(['message' => $message], $data),
                        $data['headers'] ?? []
                    );

                default:
                    $this->logError("Unhandled notification type: $type");
                    return false;
            }
        } catch (Exception $e) {
            $this->logError("Notification failed: " . $e->getMessage());
            return false;
        }
    }

    private function logError(string $message): void {
        $logEntry = sprintf(
            "[%s] ERROR: %s\n",
            date('Y-m-d H:i:s'),
            $message
        );
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }
}
