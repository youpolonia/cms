<?php
/**
 * Global Modules Manager
 * Manages reusable/global modules for JTB
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Global_Modules
{
    /**
     * Get a global module by ID
     */
    public static function get(int $id): ?array
    {
        $db = \core\Database::connection();

        $stmt = $db->prepare("
            SELECT id, name, type, content, description, thumbnail, created_at, updated_at
            FROM jtb_global_modules
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $row['content'] = json_decode($row['content'], true);

        return $row;
    }

    /**
     * Get all global modules, optionally filtered by type
     */
    public static function getAll(?string $type = null): array
    {
        $db = \core\Database::connection();

        $sql = "
            SELECT id, name, type, content, description, thumbnail, created_at, updated_at
            FROM jtb_global_modules
        ";
        $params = [];

        if ($type !== null) {
            $sql .= " WHERE type = ?";
            $params[] = $type;
        }

        $sql .= " ORDER BY name ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['content'] = json_decode($row['content'], true);
        }

        return $rows;
    }

    /**
     * Save a module as global
     *
     * @param array $data Module data with keys: id?, name, type, content, description?
     * @return int|bool Module ID on success, false on failure
     */
    public static function save(array $data): int|bool
    {
        // Validate required fields
        if (empty($data['name']) || empty($data['type']) || !isset($data['content'])) {
            return false;
        }

        // Handle content
        $content = $data['content'];
        if (is_string($content)) {
            $content = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return false;
            }
        }

        $contentJson = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $description = $data['description'] ?? null;
        $thumbnail = $data['thumbnail'] ?? null;

        $db = \core\Database::connection();

        if (!empty($data['id'])) {
            // Update existing
            $stmt = $db->prepare("
                UPDATE jtb_global_modules
                SET name = ?, type = ?, content = ?, description = ?, thumbnail = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $success = $stmt->execute([
                $data['name'],
                $data['type'],
                $contentJson,
                $description,
                $thumbnail,
                $data['id']
            ]);

            return $success ? (int) $data['id'] : false;
        } else {
            // Insert new
            $stmt = $db->prepare("
                INSERT INTO jtb_global_modules (name, type, content, description, thumbnail, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $success = $stmt->execute([
                $data['name'],
                $data['type'],
                $contentJson,
                $description,
                $thumbnail
            ]);

            return $success ? (int) $db->lastInsertId() : false;
        }
    }

    /**
     * Update a global module
     */
    public static function update(int $id, array $data): bool
    {
        $module = self::get($id);
        if (!$module) {
            return false;
        }

        $data['id'] = $id;
        return self::save($data) !== false;
    }

    /**
     * Delete a global module
     */
    public static function delete(int $id): bool
    {
        $db = \core\Database::connection();

        $stmt = $db->prepare("DELETE FROM jtb_global_modules WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Create an instance of a global module for use in page
     * Returns a copy of the module content with new IDs
     */
    public static function createInstance(int $id): ?array
    {
        $module = self::get($id);
        if (!$module) {
            return null;
        }

        // Clone content with new IDs
        $content = $module['content'];
        $content = self::regenerateIds($content);

        // Mark as global module instance
        $content['_global_module_id'] = $id;
        $content['_global_module_name'] = $module['name'];

        return $content;
    }

    /**
     * Create a linked instance that references the global module
     */
    public static function createLinkedInstance(int $id): ?array
    {
        $module = self::get($id);
        if (!$module) {
            return null;
        }

        return [
            'type' => 'global_module',
            'id' => 'gm_' . self::generateRandomId(),
            'attrs' => [
                'global_module_id' => $id,
                'linked' => true
            ],
            'children' => []
        ];
    }

    /**
     * Get modules grouped by type
     */
    public static function getGroupedByType(): array
    {
        $modules = self::getAll();

        $grouped = [];
        foreach ($modules as $module) {
            $type = $module['type'];
            if (!isset($grouped[$type])) {
                $grouped[$type] = [];
            }
            $grouped[$type][] = $module;
        }

        return $grouped;
    }

    /**
     * Get module types present in the library
     */
    public static function getTypes(): array
    {
        $db = \core\Database::connection();

        $stmt = $db->query("
            SELECT DISTINCT type, COUNT(*) as count
            FROM jtb_global_modules
            GROUP BY type
            ORDER BY type ASC
        ");

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Search modules by name
     */
    public static function search(string $query, ?string $type = null): array
    {
        $db = \core\Database::connection();

        $sql = "
            SELECT id, name, type, content, description, thumbnail, created_at, updated_at
            FROM jtb_global_modules
            WHERE name LIKE ?
        ";
        $params = ['%' . $query . '%'];

        if ($type !== null) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }

        $sql .= " ORDER BY name ASC LIMIT 50";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['content'] = json_decode($row['content'], true);
        }

        return $rows;
    }

    /**
     * Duplicate a global module
     */
    public static function duplicate(int $id, ?string $newName = null): int|bool
    {
        $module = self::get($id);
        if (!$module) {
            return false;
        }

        return self::save([
            'name' => $newName ?? $module['name'] . ' (Copy)',
            'type' => $module['type'],
            'content' => $module['content'],
            'description' => $module['description']
        ]);
    }

    /**
     * Get total count of global modules
     */
    public static function getCount(): int
    {
        $db = \core\Database::connection();

        $stmt = $db->query("SELECT COUNT(*) as count FROM jtb_global_modules");
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return (int) $row['count'];
    }

    /**
     * Regenerate IDs in content recursively
     */
    private static function regenerateIds(array $element): array
    {
        // Regenerate this element's ID
        if (isset($element['id'])) {
            $prefix = explode('_', $element['id'])[0] ?? 'el';
            $element['id'] = $prefix . '_' . self::generateRandomId();
        }

        // Recursively regenerate children IDs
        if (isset($element['children']) && is_array($element['children'])) {
            foreach ($element['children'] as $key => $child) {
                $element['children'][$key] = self::regenerateIds($child);
            }
        }

        return $element;
    }

    /**
     * Generate random ID
     */
    private static function generateRandomId(): string
    {
        return substr(bin2hex(random_bytes(4)), 0, 8);
    }

    /**
     * Update thumbnail for a module
     */
    public static function updateThumbnail(int $id, string $thumbnailUrl): bool
    {
        $db = \core\Database::connection();

        $stmt = $db->prepare("UPDATE jtb_global_modules SET thumbnail = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$thumbnailUrl, $id]);
    }

    /**
     * Check if a module name already exists
     */
    public static function nameExists(string $name, ?int $excludeId = null): bool
    {
        $db = \core\Database::connection();

        $sql = "SELECT id FROM jtb_global_modules WHERE name = ?";
        $params = [$name];

        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch() !== false;
    }
}
