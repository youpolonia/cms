<?php
class FlashMessage {
    const TYPE_SUCCESS = 'success';
    const TYPE_ERROR = 'error';
    const TYPE_WARNING = 'warning';

    public static function add(string $message, string $type = self::TYPE_SUCCESS): void {
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }
        $_SESSION['flash_messages'][] = [
            'message' => $message,
            'type' => $type
        ];
    }

    public static function get(): array {
        $messages = $_SESSION['flash_messages'] ?? [];
        self::clear();
        return $messages;
    }

    public static function clear(): void {
        unset($_SESSION['flash_messages']);
    }

    public static function hasMessages(): bool {
        return !empty($_SESSION['flash_messages']);
    }
}
