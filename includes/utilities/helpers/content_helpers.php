<?php
require_once __DIR__ . '/../../../core/database.php';

/**
 * Content Helper Functions
 */
class ContentHelpers {
    /**
     * Get all content types
     * @return array Array of content types with id, name, slug, description
     */
    public static function getAllContentTypes(): array {
        $db = \core\Database::connection();
        $stmt = $db->query("SELECT * FROM content_types ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get content type by slug
     * @param string $slug Content type slug
     * @return array|null Content type data or null if not found
     */
    public static function getContentTypeBySlug(string $slug): ?array {
        $db = \core\Database::connection();
        $stmt = $db->prepare("SELECT * FROM content_types WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Check if content type exists
     * @param string $type Content type name or slug
     * @return bool True if exists, false otherwise
     */
    public static function contentTypeExists(string $type): bool {
        $db = \core\Database::connection();
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM content_types 
            WHERE name = ? OR slug = ?
        ");
        $stmt->execute([$type, $type]);
        return (bool)$stmt->fetchColumn();
    }
}
