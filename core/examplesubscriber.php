<?php
declare(strict_types=1);

class ExampleSubscriber {
    public static function register(): void {
        NotificationService::subscribe('user.created', [self::class, 'handleUserCreated']);
        NotificationService::subscribe('content.published', [self::class, 'handleContentPublished']);
    }

    public static function handleUserCreated(array $data): void {
        // Log new user creation
        error_log("New user created: " . ($data['email'] ?? 'Unknown'));
    }

    public static function handleContentPublished(array $data): void {
        // Process published content
        error_log("Content published: " . ($data['title'] ?? 'Untitled'));
    }
}
