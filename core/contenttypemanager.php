<?php

require_once __DIR__ . '/database.php';

class ContentTypeManager {
    /**
     * Creates a new content type
     * @param array $data Content type data including name, description, fields etc.
     * @return int|false ID of created content type or false on failure
     */
    public static function createContentType(array $data): int|false {
        $pdo = \core\Database::connection();
        $query = "INSERT INTO content_types 
                 (name, description, fields, created_at, updated_at) 
                 VALUES (?, ?, ?, NOW(), NOW())";
        
        $stmt = $db->prepare($query);
        $fieldsJson = json_encode($data['fields'] ?? []);
        
        $result = $stmt->execute([
            $data['name'],
            $data['description'] ?? '',
            $fieldsJson
        ]);
        
        return $result ? $db->lastInsertId() : false;
    }

    /**
     * Gets a content type by ID
     * @param int $id Content type ID
     * @return array|null Content type data or null if not found
     */
    public static function getContentType(int $id): ?array {
        $pdo = \core\Database::connection();
        $stmt = $db->prepare("SELECT * FROM content_types WHERE id = ?");
        $stmt->execute([$id]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $result['fields'] = json_decode($result['fields'], true) ?? [];
        }
        
        return $result ?: null;
    }

    /**
     * Updates a content type
     * @param int $id Content type ID
     * @param array $data Data to update
     * @return bool True on success, false on failure
     */
    public static function updateContentType(int $id, array $data): bool {
        $pdo = \core\Database::connection();
        $query = "UPDATE content_types SET 
                 name = ?, 
                 description = ?, 
                 fields = ?, 
                 updated_at = NOW() 
                 WHERE id = ?";
        
        $stmt = $db->prepare($query);
        $fieldsJson = json_encode($data['fields'] ?? []);
        
        return $stmt->execute([
            $data['name'],
            $data['description'] ?? '',
            $fieldsJson,
            $id
        ]);
    }

    /**
     * Deletes a content type
     * @param int $id Content type ID
     * @return bool True on success, false on failure
     */
    public static function deleteContentType(int $id): bool {
        $pdo = \core\Database::connection();
        $stmt = $db->prepare("DELETE FROM content_types WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Lists all content types
     * @return array Array of content types
     */
    public static function listContentTypes(): array {
        $pdo = \core\Database::connection();
        $stmt = $db->query("SELECT * FROM content_types ORDER BY name ASC");
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($results as &$result) {
            $result['fields'] = json_decode($result['fields'], true) ?? [];
        }
        
        return $results;
    }
}
