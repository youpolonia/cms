<?php
/**
 * Templates Manager
 * Manages JTB template storage and retrieval for Theme Builder
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Templates
{
    /**
     * Valid template types
     */
    const TYPES = ['header', 'footer', 'body'];

    /**
     * Get a template by ID
     */
    public static function get(int $id): ?array
    {
        $db = \core\Database::connection();

        $stmt = $db->prepare("
            SELECT id, name, type, content, css_cache, is_default, priority, created_at, updated_at
            FROM jtb_templates
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $row['content'] = json_decode($row['content'], true);
        $row['is_default'] = (bool) $row['is_default'];

        // Get conditions
        $row['conditions'] = JTB_Template_Conditions::getForTemplate($id);

        return $row;
    }

    /**
     * Get all templates, optionally filtered by type
     */
    public static function getAll(?string $type = null): array
    {
        $db = \core\Database::connection();

        $sql = "
            SELECT id, name, type, content, css_cache, is_default, priority, created_at, updated_at
            FROM jtb_templates
        ";
        $params = [];

        if ($type !== null && in_array($type, self::TYPES)) {
            $sql .= " WHERE type = ?";
            $params[] = $type;
        }

        $sql .= " ORDER BY type ASC, is_default DESC, priority ASC, name ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['content'] = json_decode($row['content'], true);
            $row['is_default'] = (bool) $row['is_default'];
            $row['conditions'] = JTB_Template_Conditions::getForTemplate((int) $row['id']);
        }

        return $rows;
    }

    /**
     * Get default template for a type
     */
    public static function getDefault(string $type): ?array
    {
        if (!in_array($type, self::TYPES)) {
            return null;
        }

        $db = \core\Database::connection();

        $stmt = $db->prepare("
            SELECT id, name, type, content, css_cache, is_default, priority, created_at, updated_at
            FROM jtb_templates
            WHERE type = ? AND is_default = 1
            LIMIT 1
        ");
        $stmt->execute([$type]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $row['content'] = json_decode($row['content'], true);
        $row['is_default'] = true;
        $row['conditions'] = JTB_Template_Conditions::getForTemplate((int) $row['id']);

        return $row;
    }

    /**
     * Save a template (insert or update)
     *
     * @param array $data Template data with keys: id?, name, type, content, is_default?, priority?
     * @return int|bool Template ID on success, false on failure
     */
    public static function save(array $data): int|bool
    {
        // Validate required fields
        if (empty($data['name']) || empty($data['type'])) {
            return false;
        }

        if (!in_array($data['type'], self::TYPES)) {
            return false;
        }

        // Handle content
        $content = $data['content'] ?? ['version' => '1.0', 'content' => []];
        if (is_string($content)) {
            $content = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return false;
            }
        }

        // Validate content structure
        if (!self::validateContent($content)) {
            return false;
        }

        // Generate CSS cache
        $cssCache = JTB_Renderer::generateCss($content);
        $contentJson = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $db = \core\Database::connection();

        $isDefault = isset($data['is_default']) ? (int) $data['is_default'] : 0;
        $priority = isset($data['priority']) ? (int) $data['priority'] : 10;

        // If setting as default, unset other defaults of same type
        if ($isDefault) {
            $stmt = $db->prepare("UPDATE jtb_templates SET is_default = 0 WHERE type = ?");
            $stmt->execute([$data['type']]);
        }

        if (!empty($data['id'])) {
            // Update existing
            $stmt = $db->prepare("
                UPDATE jtb_templates
                SET name = ?, type = ?, content = ?, css_cache = ?, is_default = ?, priority = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $success = $stmt->execute([
                $data['name'],
                $data['type'],
                $contentJson,
                $cssCache,
                $isDefault,
                $priority,
                $data['id']
            ]);

            return $success ? (int) $data['id'] : false;
        } else {
            // Insert new
            $stmt = $db->prepare("
                INSERT INTO jtb_templates (name, type, content, css_cache, is_default, priority, is_active, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 1, NOW())
            ");
            $success = $stmt->execute([
                $data['name'],
                $data['type'],
                $contentJson,
                $cssCache,
                $isDefault,
                $priority
            ]);

            return $success ? (int) $db->lastInsertId() : false;
        }
    }

    /**
     * Delete a template
     */
    public static function delete(int $id): bool
    {
        $db = \core\Database::connection();

        // Conditions are deleted automatically via CASCADE
        $stmt = $db->prepare("DELETE FROM jtb_templates WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Set a template as default for its type
     */
    public static function setDefault(int $id): bool
    {
        $template = self::get($id);
        if (!$template) {
            return false;
        }

        $db = \core\Database::connection();

        // Unset all defaults of same type
        $stmt = $db->prepare("UPDATE jtb_templates SET is_default = 0 WHERE type = ?");
        $stmt->execute([$template['type']]);

        // Set this template as default
        $stmt = $db->prepare("UPDATE jtb_templates SET is_default = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Unset default for a template
     */
    public static function unsetDefault(int $id): bool
    {
        $db = \core\Database::connection();

        $stmt = $db->prepare("UPDATE jtb_templates SET is_default = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Duplicate a template
     */
    public static function duplicate(int $id, ?string $newName = null): int|bool
    {
        $template = self::get($id);
        if (!$template) {
            return false;
        }

        $newData = [
            'name' => $newName ?? $template['name'] . ' (Copy)',
            'type' => $template['type'],
            'content' => $template['content'],
            'is_default' => false,
            'priority' => $template['priority']
        ];

        $newId = self::save($newData);

        if ($newId) {
            // Duplicate conditions
            foreach ($template['conditions'] as $condition) {
                JTB_Template_Conditions::add(
                    $newId,
                    $condition['condition_type'],
                    $condition['page_type'],
                    $condition['object_id']
                );
            }
        }

        return $newId;
    }

    /**
     * Validate template content structure
     */
    public static function validateContent(array $content): bool
    {
        // Must have version
        if (!isset($content['version'])) {
            return false;
        }

        // Must have content array
        if (!isset($content['content']) || !is_array($content['content'])) {
            return false;
        }

        return true;
    }

    /**
     * Regenerate CSS cache for a template
     */
    public static function regenerateCss(int $id): bool
    {
        $template = self::get($id);
        if (!$template) {
            return false;
        }

        $cssCache = JTB_Renderer::generateCss($template['content']);

        $db = \core\Database::connection();

        $stmt = $db->prepare("UPDATE jtb_templates SET css_cache = ? WHERE id = ?");
        return $stmt->execute([$cssCache, $id]);
    }

    /**
     * Regenerate CSS for all templates
     */
    public static function regenerateAllCss(): int
    {
        $templates = self::getAll();
        $count = 0;

        foreach ($templates as $template) {
            if (self::regenerateCss((int) $template['id'])) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get templates grouped by type
     */
    public static function getGroupedByType(): array
    {
        $templates = self::getAll();

        $grouped = [
            'header' => [],
            'footer' => [],
            'body' => []
        ];

        foreach ($templates as $template) {
            $grouped[$template['type']][] = $template;
        }

        return $grouped;
    }

    /**
     * Get templates count by type
     */
    public static function getCountByType(): array
    {
        $db = \core\Database::connection();

        $stmt = $db->query("
            SELECT type, COUNT(*) as count
            FROM jtb_templates
            GROUP BY type
        ");

        $counts = [
            'header' => 0,
            'footer' => 0,
            'body' => 0,
            'total' => 0
        ];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $counts[$row['type']] = (int) $row['count'];
            $counts['total'] += (int) $row['count'];
        }

        return $counts;
    }

    /**
     * Get empty template content structure
     */
    public static function getEmptyContent(): array
    {
        return [
            'version' => '1.0',
            'content' => []
        ];
    }
}
