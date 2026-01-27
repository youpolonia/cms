<?php
/**
 * Layout Gallery Manager
 * Manages row structure layouts - like Divi's Column Structure Picker
 *
 * This is a ROW STRUCTURE GALLERY - not full page templates!
 * Users select column arrangements when adding rows.
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Layout_Gallery
{
    /**
     * Check if tables exist
     */
    public static function tablesExist(): bool
    {
        try {
            $db = \core\Database::connection();
            $stmt = $db->query("SHOW TABLES LIKE 'jtb_layouts'");
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Create database tables
     */
    public static function createTables(): void
    {
        $db = \core\Database::connection();

        // Table: Layouts (row structure layouts like Divi)
        $db->exec("
            CREATE TABLE IF NOT EXISTS jtb_layouts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL,
                description TEXT,
                category VARCHAR(100) DEFAULT 'rows',
                layout_type VARCHAR(50) DEFAULT 'row',
                column_structure VARCHAR(100),
                content JSON NOT NULL,
                thumbnail VARCHAR(500),
                is_premade TINYINT(1) DEFAULT 0,
                sort_order INT DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_category (category),
                INDEX idx_type (layout_type),
                INDEX idx_premade (is_premade)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Seed default layouts if empty
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM jtb_layouts WHERE is_premade = 1");
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ((int)$row['cnt'] === 0) {
            self::seedDefaultLayouts();
        }
    }

    /**
     * Seed default row structures - EXACTLY like Divi's 20 column layouts
     */
    private static function seedDefaultLayouts(): void
    {
        // =====================================================
        // ROW STRUCTURES - Divi's 20 Column Layout Options
        // =====================================================
        $rowLayouts = [
            // =========== EQUAL WIDTH LAYOUTS (6) ===========
            [
                'name' => '1 Column',
                'slug' => 'row-1-col',
                'category' => 'equal',
                'column_structure' => '1',
                'sort_order' => 1,
            ],
            [
                'name' => '2 Columns',
                'slug' => 'row-2-col',
                'category' => 'equal',
                'column_structure' => '1/2,1/2',
                'sort_order' => 2,
            ],
            [
                'name' => '3 Columns',
                'slug' => 'row-3-col',
                'category' => 'equal',
                'column_structure' => '1/3,1/3,1/3',
                'sort_order' => 3,
            ],
            [
                'name' => '4 Columns',
                'slug' => 'row-4-col',
                'category' => 'equal',
                'column_structure' => '1/4,1/4,1/4,1/4',
                'sort_order' => 4,
            ],
            [
                'name' => '5 Columns',
                'slug' => 'row-5-col',
                'category' => 'equal',
                'column_structure' => '1/5,1/5,1/5,1/5,1/5',
                'sort_order' => 5,
            ],
            [
                'name' => '6 Columns',
                'slug' => 'row-6-col',
                'category' => 'equal',
                'column_structure' => '1/6,1/6,1/6,1/6,1/6,1/6',
                'sort_order' => 6,
            ],

            // =========== UNEQUAL 2-COLUMN LAYOUTS (6) ===========
            [
                'name' => '2/5 + 3/5',
                'slug' => 'row-2-5-3-5',
                'category' => 'unequal-2',
                'column_structure' => '2/5,3/5',
                'sort_order' => 7,
            ],
            [
                'name' => '3/5 + 2/5',
                'slug' => 'row-3-5-2-5',
                'category' => 'unequal-2',
                'column_structure' => '3/5,2/5',
                'sort_order' => 8,
            ],
            [
                'name' => '1/3 + 2/3',
                'slug' => 'row-1-3-2-3',
                'category' => 'unequal-2',
                'column_structure' => '1/3,2/3',
                'sort_order' => 9,
            ],
            [
                'name' => '2/3 + 1/3',
                'slug' => 'row-2-3-1-3',
                'category' => 'unequal-2',
                'column_structure' => '2/3,1/3',
                'sort_order' => 10,
            ],
            [
                'name' => '1/4 + 3/4',
                'slug' => 'row-1-4-3-4',
                'category' => 'unequal-2',
                'column_structure' => '1/4,3/4',
                'sort_order' => 11,
            ],
            [
                'name' => '3/4 + 1/4',
                'slug' => 'row-3-4-1-4',
                'category' => 'unequal-2',
                'column_structure' => '3/4,1/4',
                'sort_order' => 12,
            ],

            // =========== UNEQUAL 3-COLUMN LAYOUTS (6) ===========
            [
                'name' => '1/4 + 1/2 + 1/4',
                'slug' => 'row-1-4-1-2-1-4',
                'category' => 'unequal-3',
                'column_structure' => '1/4,1/2,1/4',
                'sort_order' => 13,
            ],
            [
                'name' => '1/5 + 3/5 + 1/5',
                'slug' => 'row-1-5-3-5-1-5',
                'category' => 'unequal-3',
                'column_structure' => '1/5,3/5,1/5',
                'sort_order' => 14,
            ],
            [
                'name' => '1/4 + 1/4 + 1/2',
                'slug' => 'row-1-4-1-4-1-2',
                'category' => 'unequal-3',
                'column_structure' => '1/4,1/4,1/2',
                'sort_order' => 15,
            ],
            [
                'name' => '1/2 + 1/4 + 1/4',
                'slug' => 'row-1-2-1-4-1-4',
                'category' => 'unequal-3',
                'column_structure' => '1/2,1/4,1/4',
                'sort_order' => 16,
            ],
            [
                'name' => '1/5 + 1/5 + 3/5',
                'slug' => 'row-1-5-1-5-3-5',
                'category' => 'unequal-3',
                'column_structure' => '1/5,1/5,3/5',
                'sort_order' => 17,
            ],
            [
                'name' => '3/5 + 1/5 + 1/5',
                'slug' => 'row-3-5-1-5-1-5',
                'category' => 'unequal-3',
                'column_structure' => '3/5,1/5,1/5',
                'sort_order' => 18,
            ],

            // =========== UNEQUAL 4-COLUMN LAYOUTS (2) ===========
            [
                'name' => '1/6 + 1/6 + 1/6 + 1/2',
                'slug' => 'row-1-6-1-6-1-6-1-2',
                'category' => 'unequal-4',
                'column_structure' => '1/6,1/6,1/6,1/2',
                'sort_order' => 19,
            ],
            [
                'name' => '1/2 + 1/6 + 1/6 + 1/6',
                'slug' => 'row-1-2-1-6-1-6-1-6',
                'category' => 'unequal-4',
                'column_structure' => '1/2,1/6,1/6,1/6',
                'sort_order' => 20,
            ],
        ];

        // Save all layouts
        foreach ($rowLayouts as $layout) {
            // Build content structure
            $cols = self::parseColumnStructure($layout['column_structure']);
            $content = [
                'version' => '1.0',
                'content' => [
                    [
                        'type' => 'row',
                        'id' => self::generateId(),
                        'attrs' => ['columns' => $layout['column_structure']],
                        'children' => array_map(function($width) {
                            return [
                                'type' => 'column',
                                'id' => self::generateId(),
                                'attrs' => ['width' => $width],
                                'children' => []
                            ];
                        }, $cols)
                    ]
                ]
            ];

            self::save([
                'name' => $layout['name'],
                'slug' => $layout['slug'],
                'category' => $layout['category'],
                'layout_type' => 'row',
                'column_structure' => $layout['column_structure'],
                'content' => $content,
                'is_premade' => 1,
                'sort_order' => $layout['sort_order']
            ]);
        }
    }

    /**
     * Parse column structure string to array of widths
     * e.g. "1/3,2/3" -> ["1/3", "2/3"]
     */
    private static function parseColumnStructure(string $structure): array
    {
        return array_map('trim', explode(',', $structure));
    }

    /**
     * Generate unique ID
     */
    private static function generateId(): string
    {
        return 'jtb_' . substr(md5(uniqid(mt_rand(), true)), 0, 8);
    }

    /**
     * Get layout by ID
     */
    public static function get(int $id): ?array
    {
        $db = \core\Database::connection();
        $stmt = $db->prepare("SELECT * FROM jtb_layouts WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) return null;

        $row['content'] = json_decode($row['content'], true);
        $row['is_premade'] = (bool)$row['is_premade'];
        return $row;
    }

    /**
     * Get all layouts with filters
     */
    public static function getAll(array $filters = []): array
    {
        $db = \core\Database::connection();

        $sql = "SELECT * FROM jtb_layouts WHERE 1=1";
        $params = [];

        if (!empty($filters['category'])) {
            $sql .= " AND category = ?";
            $params[] = $filters['category'];
        }

        if (!empty($filters['layout_type'])) {
            $sql .= " AND layout_type = ?";
            $params[] = $filters['layout_type'];
        }

        if (isset($filters['is_premade'])) {
            $sql .= " AND is_premade = ?";
            $params[] = (int)$filters['is_premade'];
        }

        $sql .= " ORDER BY sort_order ASC, id ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $layouts = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['content'] = json_decode($row['content'], true);
            $row['is_premade'] = (bool)$row['is_premade'];
            $layouts[] = $row;
        }

        return $layouts;
    }

    /**
     * Get categories (for filtering)
     */
    public static function getCategories(): array
    {
        return [
            'equal' => 'Equal Width',
            'unequal-2' => '2 Columns (Unequal)',
            'unequal-3' => '3 Columns (Unequal)',
            'unequal-4' => '4 Columns (Unequal)',
        ];
    }

    /**
     * Save layout
     */
    public static function save(array $data): int|false
    {
        $db = \core\Database::connection();

        $content = $data['content'] ?? [];
        if (is_array($content)) {
            $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        }

        if (!empty($data['id'])) {
            $stmt = $db->prepare("
                UPDATE jtb_layouts
                SET name = ?, slug = ?, description = ?, category = ?,
                    layout_type = ?, column_structure = ?, content = ?,
                    thumbnail = ?, sort_order = ?
                WHERE id = ? AND is_premade = 0
            ");
            $success = $stmt->execute([
                $data['name'],
                $data['slug'] ?? self::generateSlug($data['name']),
                $data['description'] ?? null,
                $data['category'] ?? 'equal',
                $data['layout_type'] ?? 'row',
                $data['column_structure'] ?? null,
                $content,
                $data['thumbnail'] ?? null,
                $data['sort_order'] ?? 0,
                $data['id']
            ]);
            return $success ? (int)$data['id'] : false;
        } else {
            $stmt = $db->prepare("
                INSERT INTO jtb_layouts
                (name, slug, description, category, layout_type, column_structure,
                 content, thumbnail, is_premade, sort_order, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $success = $stmt->execute([
                $data['name'],
                $data['slug'] ?? self::generateSlug($data['name']),
                $data['description'] ?? null,
                $data['category'] ?? 'equal',
                $data['layout_type'] ?? 'row',
                $data['column_structure'] ?? null,
                $content,
                $data['thumbnail'] ?? null,
                (int)($data['is_premade'] ?? 0),
                $data['sort_order'] ?? 0
            ]);
            return $success ? (int)$db->lastInsertId() : false;
        }
    }

    /**
     * Delete layout (only user-created)
     */
    public static function delete(int $id): bool
    {
        $db = \core\Database::connection();
        $stmt = $db->prepare("DELETE FROM jtb_layouts WHERE id = ? AND is_premade = 0");
        return $stmt->execute([$id]);
    }

    /**
     * Generate slug from name
     */
    private static function generateSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    /**
     * Reset and reseed layouts
     */
    public static function reseed(): void
    {
        $db = \core\Database::connection();
        $db->exec("DELETE FROM jtb_layouts WHERE is_premade = 1");
        self::seedDefaultLayouts();
    }
}
