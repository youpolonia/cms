<?php
/**
 * Builder Manager
 * Manages JTB content storage and retrieval
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Builder
{
    /**
     * Get JTB content for a post
     */
    public static function getContent(int $postId): ?array
    {
        $db = \core\Database::connection();

        $stmt = $db->prepare("SELECT content FROM jtb_pages WHERE post_id = ?");
        $stmt->execute([$postId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $content = json_decode($row['content'], true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $content;
    }

    /**
     * Save JTB content for a post
     */
    public static function saveContent(int $postId, $content): bool
    {
        // Handle string input
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

        $db = \core\Database::connection();

        $contentJson = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Check if record exists
        $stmt = $db->prepare("SELECT id FROM jtb_pages WHERE post_id = ?");
        $stmt->execute([$postId]);
        $exists = $stmt->fetch();

        if ($exists) {
            // Update
            $stmt = $db->prepare("
                UPDATE jtb_pages
                SET content = ?, css_cache = ?, updated_at = NOW()
                WHERE post_id = ?
            ");
            return $stmt->execute([$contentJson, $cssCache, $postId]);
        } else {
            // Insert
            $stmt = $db->prepare("
                INSERT INTO jtb_pages (post_id, content, css_cache, version)
                VALUES (?, ?, ?, '1.0')
            ");
            return $stmt->execute([$postId, $contentJson, $cssCache]);
        }
    }

    /**
     * Delete JTB content for a post
     */
    public static function deleteContent(int $postId): bool
    {
        $db = \core\Database::connection();

        $stmt = $db->prepare("DELETE FROM jtb_pages WHERE post_id = ?");
        return $stmt->execute([$postId]);
    }

    /**
     * Check if post has JTB content
     */
    public static function hasContent(int $postId): bool
    {
        $db = \core\Database::connection();

        $stmt = $db->prepare("SELECT id FROM jtb_pages WHERE post_id = ?");
        $stmt->execute([$postId]);

        return $stmt->fetch() !== false;
    }

    /**
     * Validate content structure
     */
    public static function validateContent(array $content): bool
    {
        $logFile = '/tmp/jtb_render.log';

        // Must have version
        if (!isset($content['version'])) {
            file_put_contents($logFile, "[VALIDATE] FAIL: missing version\n", FILE_APPEND);
            return false;
        }

        // Must have content array
        if (!isset($content['content']) || !is_array($content['content'])) {
            file_put_contents($logFile, "[VALIDATE] FAIL: missing content array\n", FILE_APPEND);
            return false;
        }

        file_put_contents($logFile, "[VALIDATE] Checking " . count($content['content']) . " sections\n", FILE_APPEND);

        // Validate each section
        foreach ($content['content'] as $index => $section) {
            if (!self::validateSection($section, $logFile)) {
                file_put_contents($logFile, "[VALIDATE] FAIL: section $index failed\n", FILE_APPEND);
                file_put_contents($logFile, "[VALIDATE] Section keys: " . implode(',', array_keys($section)) . "\n", FILE_APPEND);
                file_put_contents($logFile, "[VALIDATE] Section type: " . ($section['type'] ?? 'null') . ", id: " . ($section['id'] ?? 'null') . "\n", FILE_APPEND);
                return false;
            }
        }

        file_put_contents($logFile, "[VALIDATE] ALL SECTIONS PASSED\n", FILE_APPEND);
        return true;
    }

    /**
     * Validate section structure
     */
    private static function validateSection(array $section, string $logFile = '/tmp/jtb_render.log'): bool
    {
        // Must have type
        if (!isset($section['type']) || $section['type'] !== 'section') {
            file_put_contents($logFile, "[SECTION] FAIL: type is '" . ($section['type'] ?? 'null') . "'\n", FILE_APPEND);
            return false;
        }

        // Must have id
        if (empty($section['id'])) {
            file_put_contents($logFile, "[SECTION] FAIL: missing id. Keys: " . implode(',', array_keys($section)) . "\n", FILE_APPEND);
            return false;
        }

        // Children must be rows
        if (isset($section['children']) && is_array($section['children'])) {
            foreach ($section['children'] as $rowIndex => $row) {
                if (!self::validateRow($row, $logFile)) {
                    file_put_contents($logFile, "[SECTION] FAIL: row $rowIndex failed\n", FILE_APPEND);
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Validate row structure
     */
    private static function validateRow(array $row, string $logFile = '/tmp/jtb_render.log'): bool
    {
        // Must have type
        if (!isset($row['type']) || $row['type'] !== 'row') {
            file_put_contents($logFile, "[ROW] FAIL: type is '" . ($row['type'] ?? 'null') . "'\n", FILE_APPEND);
            return false;
        }

        // Must have id
        if (empty($row['id'])) {
            file_put_contents($logFile, "[ROW] FAIL: missing id\n", FILE_APPEND);
            return false;
        }

        // Children must be columns
        if (isset($row['children']) && is_array($row['children'])) {
            foreach ($row['children'] as $colIndex => $column) {
                if (!self::validateColumn($column, $logFile)) {
                    file_put_contents($logFile, "[ROW] FAIL: column $colIndex failed\n", FILE_APPEND);
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Validate column structure
     */
    private static function validateColumn(array $column, string $logFile = '/tmp/jtb_render.log'): bool
    {
        // Must have type
        if (!isset($column['type']) || $column['type'] !== 'column') {
            file_put_contents($logFile, "[COLUMN] FAIL: type is '" . ($column['type'] ?? 'null') . "'\n", FILE_APPEND);
            return false;
        }

        // Must have id
        if (empty($column['id'])) {
            file_put_contents($logFile, "[COLUMN] FAIL: missing id\n", FILE_APPEND);
            return false;
        }

        // Children must be valid modules
        if (isset($column['children']) && is_array($column['children'])) {
            foreach ($column['children'] as $modIndex => $module) {
                if (!self::validateModule($module, $logFile)) {
                    file_put_contents($logFile, "[COLUMN] FAIL: module $modIndex failed\n", FILE_APPEND);
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Validate module structure
     */
    private static function validateModule(array $module, string $logFile = '/tmp/jtb_render.log'): bool
    {
        // Must have type
        if (empty($module['type'])) {
            file_put_contents($logFile, "[MODULE] FAIL: empty type\n", FILE_APPEND);
            return false;
        }

        // Must have id
        if (empty($module['id'])) {
            file_put_contents($logFile, "[MODULE] FAIL: missing id for type '" . $module['type'] . "'\n", FILE_APPEND);
            return false;
        }

        // Type must be registered (skip for structure types)
        $structureTypes = ['section', 'row', 'column'];
        if (!in_array($module['type'], $structureTypes)) {
            if (!JTB_Registry::exists($module['type'])) {
                file_put_contents($logFile, "[MODULE] FAIL: unregistered type '" . $module['type'] . "'\n", FILE_APPEND);
                return false;
            }
        }

        return true;
    }

    /**
     * Get empty content structure
     */
    public static function getEmptyContent(): array
    {
        return [
            'version' => '1.0',
            'content' => []
        ];
    }

    /**
     * Get default section structure
     */
    public static function getDefaultSection(): array
    {
        return [
            'type' => 'section',
            'id' => 'section_' . self::generateRandomId(),
            'attrs' => [
                'fullwidth' => false,
                'inner_width' => 1200
            ],
            'children' => [
                self::getDefaultRow()
            ]
        ];
    }

    /**
     * Get default row structure
     */
    public static function getDefaultRow(): array
    {
        return [
            'type' => 'row',
            'id' => 'row_' . self::generateRandomId(),
            'attrs' => [
                'columns' => '1',
                'column_gap' => 30,
                'equal_heights' => true
            ],
            'children' => [
                self::getDefaultColumn()
            ]
        ];
    }

    /**
     * Get default column structure
     */
    public static function getDefaultColumn(): array
    {
        return [
            'type' => 'column',
            'id' => 'column_' . self::generateRandomId(),
            'attrs' => [],
            'children' => []
        ];
    }

    /**
     * Generate random ID
     */
    private static function generateRandomId(): string
    {
        return substr(bin2hex(random_bytes(4)), 0, 8);
    }
}
