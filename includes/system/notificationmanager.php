<?php
class NotificationManager {
    private static $queueFile = __DIR__.'/../../logs/notifications.json';
    private static $maxQueueSize = 500;

    public static function queueNotification(string $type, string $message, array $context = []): bool {
        $notifications = self::loadNotifications();
        
        if (count($notifications) >= self::$maxQueueSize) {
            array_shift($notifications);
        }

        $notifications[] = [
            'id' => uniqid(),
            'type' => self::sanitizeType($type),
            'message' => htmlspecialchars($message, ENT_QUOTES),
            'context' => self::sanitizeContext($context),
            'timestamp' => time(),
            'read' => false
        ];

        return file_put_contents(self::$queueFile, json_encode($notifications, JSON_PRETTY_PRINT)) !== false;
    }

    public static function getQueuedNotifications(): array {
        return self::loadNotifications();
    }

    public static function clearNotification(string $id): bool {
        $notifications = array_filter(
            self::loadNotifications(),
            fn($n) => $n['id'] !== $id
        );
        return file_put_contents(self::$queueFile, json_encode($notifications, JSON_PRETTY_PRINT)) !== false;
    }

    private static function loadNotifications(): array {
        if (!file_exists(self::$queueFile)) {
            file_put_contents(self::$queueFile, '[]');
            return [];
        }
        return json_decode(file_get_contents(self::$queueFile), true) ?: [];
    }

    private static function sanitizeType(string $type): string {
        $allowedTypes = ['info', 'warning', 'error', 'system'];
        return in_array($type, $allowedTypes) ? $type : 'info';
    }

    private static function sanitizeContext(array $context): array {
        return array_map(fn($v) => is_string($v) ? htmlspecialchars($v, ENT_QUOTES) : $v, $context);
    }
}
