<?php

class FlashMessage {
    const TYPE_SUCCESS = 'success';
    const TYPE_ERROR = 'error';
    const TYPE_WARNING = 'warning';
    const TYPE_INFO = 'info';
    
    // Workflow specific types
    const TYPE_SUBMIT = 'submit';
    const TYPE_APPROVE = 'approve';
    const TYPE_REJECT = 'reject';

    /**
     * Add a flash message to session
     * @param string $type One of the TYPE_* constants
     * @param string $message The message content
     * @param array $data Additional data for the message
     */
    public static function add(string $type, string $message, array $data = []): void {
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }
        
        $_SESSION['flash_messages'][] = [
            'type' => $type,
            'message' => $message,
            'data' => $data,
            'timestamp' => time()
        ];
    }

    /**
     * Get all flash messages and clear them from session
     * @return array Array of flash messages
     */
    public static function get(): array {
        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $messages;
    }

    /**
     * Workflow action helper methods
     */
    public static function submit(string $message, array $data = []): void {
        self::add(self::TYPE_SUBMIT, $message, $data);
    }

    public static function approve(string $message, array $data = []): void {
        self::add(self::TYPE_APPROVE, $message, $data);
    }

    public static function reject(string $message, array $data = []): void {
        self::add(self::TYPE_REJECT, $message, $data);
    }

    /**
     * Backward compatibility with old flash message system
     */
    public static function legacyAdd(string $type, string $message): void {
        self::add($type, $message);
    }
}
