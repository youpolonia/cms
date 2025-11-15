<?php
// DBSupport.php - Content Type Registry Implementation
// Version: 1.0
// Date: 2025-07-01

class DBSupport {
    private static $contentTypes = [];
    private static $initialized = false;

    public static function initialize(): void {
        if (self::$initialized) {
            return;
        }

        // Register default content types
        self::registerContentType('blog', [
            'table' => 'content_blog',
            'fields' => ['title', 'content', 'excerpt', 'featured_image'],
            'required' => ['title', 'content']
        ]);

        self::registerContentType('gallery', [
            'table' => 'content_gallery',
            'fields' => ['title', 'description', 'images'],
            'required' => ['title', 'images']
        ]);

        self::$initialized = true;
    }

    public static function registerContentType(string $type, array $config): void {
        self::$contentTypes[$type] = [
            'table' => $config['table'] ?? 'content_'.$type,
            'fields' => $config['fields'] ?? [],
            'required' => $config['required'] ?? []
        ];
    }

    public static function getContentTypeConfig(string $type): ?array {
        self::initialize();
        return self::$contentTypes[$type] ?? null;
    }

    public static function isValidContentType(string $type): bool {
        self::initialize();
        return isset(self::$contentTypes[$type]);
    }

    public static function getContentTypes(): array {
        self::initialize();
        return array_keys(self::$contentTypes);
    }

    // Existing methods from ContentController that need to be moved here
    public static function createContentForTenant(int $tenantId, array $input): int {
        // Implementation will be added in next chunk
        return 0;
    }

    public static function getContentById(int $tenantId, int $contentId): ?array {
        // Implementation will be added in next chunk
        return null;
    }

    // Additional methods will be implemented in subsequent chunks
}
