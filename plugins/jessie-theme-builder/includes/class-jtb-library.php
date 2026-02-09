<?php
/**
 * Template Library Manager
 * Manages premade and user templates for JTB
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Library
{
    /**
     * Create database tables if not exist
     */
    public static function createTables(): void
    {
        $db = \core\Database::connection();

        // Table 1: Library Categories
        $db->exec("
            CREATE TABLE IF NOT EXISTS jtb_library_categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(100) NOT NULL UNIQUE,
                description TEXT,
                icon VARCHAR(50),
                sort_order INT DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Table 2: Library Templates
        $db->exec("
            CREATE TABLE IF NOT EXISTS jtb_library_templates (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL,
                description TEXT,
                category_id INT,
                category_slug VARCHAR(100),
                tags JSON,
                content JSON NOT NULL,
                thumbnail VARCHAR(500),
                is_premade TINYINT(1) DEFAULT 0,
                is_featured TINYINT(1) DEFAULT 0,
                template_type VARCHAR(50) DEFAULT 'page',
                author VARCHAR(255),
                downloads INT DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_category (category_slug),
                INDEX idx_premade (is_premade),
                INDEX idx_type (template_type),
                INDEX idx_featured (is_featured),
                FULLTEXT idx_search (name, description)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Insert default categories if empty
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM jtb_library_categories");
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ((int)$row['cnt'] === 0) {
            self::seedCategories();
        }
    }

    /**
     * Seed default categories
     */
    private static function seedCategories(): void
    {
        $db = \core\Database::connection();

        $categories = [
            ['name' => 'Landing Pages', 'slug' => 'landing-page', 'icon' => 'layout', 'sort_order' => 1],
            ['name' => 'Business', 'slug' => 'business', 'icon' => 'briefcase', 'sort_order' => 2],
            ['name' => 'Portfolio', 'slug' => 'portfolio', 'icon' => 'grid', 'sort_order' => 3],
            ['name' => 'Blog', 'slug' => 'blog', 'icon' => 'file-text', 'sort_order' => 4],
            ['name' => 'E-commerce', 'slug' => 'ecommerce', 'icon' => 'shopping-cart', 'sort_order' => 5],
            ['name' => 'Services', 'slug' => 'services', 'icon' => 'tool', 'sort_order' => 6],
            ['name' => 'Coming Soon', 'slug' => 'coming-soon', 'icon' => 'clock', 'sort_order' => 7],
            ['name' => 'Contact', 'slug' => 'contact', 'icon' => 'mail', 'sort_order' => 8],
            ['name' => 'About', 'slug' => 'about', 'icon' => 'users', 'sort_order' => 9],
            ['name' => 'Sections', 'slug' => 'sections', 'icon' => 'layers', 'sort_order' => 10],
        ];

        $stmt = $db->prepare("
            INSERT INTO jtb_library_categories (name, slug, icon, sort_order)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($categories as $cat) {
            $stmt->execute([$cat['name'], $cat['slug'], $cat['icon'], $cat['sort_order']]);
        }
    }

    /**
     * Get a template by ID
     */
    public static function get(int $id): ?array
    {
        $db = \core\Database::connection();

        $stmt = $db->prepare("
            SELECT id, name, slug, description, category_id, category_slug, tags, content,
                   thumbnail, is_premade, is_featured, template_type, author, downloads,
                   created_at, updated_at
            FROM jtb_library_templates
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $row['content'] = $row['content'] ? json_decode($row['content'], true) : null;
        $row['tags'] = $row['tags'] ? json_decode($row['tags'], true) : [];
        $row['is_premade'] = (bool)$row['is_premade'];
        $row['is_featured'] = (bool)$row['is_featured'];

        return $row;
    }

    /**
     * Get all templates with optional filtering
     */
    public static function getAll(array $filters = []): array
    {
        $db = \core\Database::connection();

        $sql = "
            SELECT id, name, slug, description, category_id, category_slug, tags, content,
                   thumbnail, is_premade, is_featured, template_type, author, downloads,
                   created_at, updated_at
            FROM jtb_library_templates
            WHERE 1=1
        ";
        $params = [];

        // Filter by category
        if (!empty($filters['category'])) {
            $sql .= " AND category_slug = ?";
            $params[] = $filters['category'];
        }

        // Filter by template type (page, section, row)
        if (!empty($filters['type'])) {
            $sql .= " AND template_type = ?";
            $params[] = $filters['type'];
        }

        // Filter by premade/user
        if (isset($filters['is_premade'])) {
            $sql .= " AND is_premade = ?";
            $params[] = $filters['is_premade'] ? 1 : 0;
        }

        // Filter featured only
        if (!empty($filters['featured'])) {
            $sql .= " AND is_featured = 1";
        }

        // Search by name/description
        if (!empty($filters['search'])) {
            $sql .= " AND (name LIKE ? OR description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Sorting
        $orderBy = $filters['order_by'] ?? 'name';
        $orderDir = strtoupper($filters['order_dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

        $allowedOrderBy = ['name', 'created_at', 'downloads', 'updated_at'];
        if (!in_array($orderBy, $allowedOrderBy)) {
            $orderBy = 'name';
        }

        $sql .= " ORDER BY is_featured DESC, {$orderBy} {$orderDir}";

        // Limit
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];

            if (!empty($filters['offset'])) {
                $sql .= " OFFSET " . (int)$filters['offset'];
            }
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['content'] = $row['content'] ? json_decode($row['content'], true) : null;
            $row['tags'] = $row['tags'] ? json_decode($row['tags'], true) : [];
            $row['is_premade'] = (bool)$row['is_premade'];
            $row['is_featured'] = (bool)$row['is_featured'];
        }

        return $rows;
    }

    /**
     * Save a template (insert or update)
     *
     * @param array $data Template data
     * @return int|bool Template ID on success, false on failure
     */
    public static function save(array $data): int|bool
    {
        // Validate required fields
        if (empty($data['name']) || !isset($data['content'])) {
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

        // Handle tags
        $tags = $data['tags'] ?? [];
        if (is_string($tags)) {
            $tags = json_decode($tags, true) ?? [];
        }
        $tagsJson = json_encode($tags, JSON_UNESCAPED_UNICODE);

        // Generate slug if not provided
        $slug = $data['slug'] ?? self::generateSlug($data['name']);

        $db = \core\Database::connection();

        if (!empty($data['id'])) {
            // Update existing
            $stmt = $db->prepare("
                UPDATE jtb_library_templates
                SET name = ?, slug = ?, description = ?, category_slug = ?, tags = ?,
                    content = ?, thumbnail = ?, is_premade = ?, is_featured = ?,
                    template_type = ?, author = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $success = $stmt->execute([
                $data['name'],
                $slug,
                $data['description'] ?? null,
                $data['category_slug'] ?? null,
                $tagsJson,
                $contentJson,
                $data['thumbnail'] ?? null,
                (int)($data['is_premade'] ?? 0),
                (int)($data['is_featured'] ?? 0),
                $data['template_type'] ?? 'page',
                $data['author'] ?? null,
                $data['id']
            ]);

            return $success ? (int)$data['id'] : false;
        } else {
            // Insert new
            $stmt = $db->prepare("
                INSERT INTO jtb_library_templates
                (name, slug, description, category_slug, tags, content, thumbnail,
                 is_premade, is_featured, template_type, author, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $success = $stmt->execute([
                $data['name'],
                $slug,
                $data['description'] ?? null,
                $data['category_slug'] ?? null,
                $tagsJson,
                $contentJson,
                $data['thumbnail'] ?? null,
                (int)($data['is_premade'] ?? 0),
                (int)($data['is_featured'] ?? 0),
                $data['template_type'] ?? 'page',
                $data['author'] ?? null
            ]);

            return $success ? (int)$db->lastInsertId() : false;
        }
    }

    /**
     * Delete a template
     */
    public static function delete(int $id): bool
    {
        // Don't allow deleting premade templates
        $template = self::get($id);
        if ($template && $template['is_premade']) {
            return false;
        }

        $db = \core\Database::connection();
        $stmt = $db->prepare("DELETE FROM jtb_library_templates WHERE id = ? AND is_premade = 0");
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

        return self::save([
            'name' => $newName ?? $template['name'] . ' (Copy)',
            'description' => $template['description'],
            'category_slug' => $template['category_slug'],
            'tags' => $template['tags'],
            'content' => $template['content'],
            'thumbnail' => $template['thumbnail'],
            'is_premade' => 0, // Copies are always user templates
            'is_featured' => 0,
            'template_type' => $template['template_type'],
            'author' => $template['author']
        ]);
    }

    /**
     * Increment download counter
     */
    public static function incrementDownloads(int $id): bool
    {
        $db = \core\Database::connection();
        $stmt = $db->prepare("UPDATE jtb_library_templates SET downloads = downloads + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get all categories
     */
    public static function getCategories(): array
    {
        $db = \core\Database::connection();

        $stmt = $db->query("
            SELECT c.*, COUNT(t.id) as template_count
            FROM jtb_library_categories c
            LEFT JOIN jtb_library_templates t ON t.category_slug = c.slug
            GROUP BY c.id
            ORDER BY c.sort_order ASC
        ");

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get category by slug
     */
    public static function getCategory(string $slug): ?array
    {
        $db = \core\Database::connection();

        $stmt = $db->prepare("SELECT * FROM jtb_library_categories WHERE slug = ?");
        $stmt->execute([$slug]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Get template count
     */
    public static function getCount(array $filters = []): int
    {
        $db = \core\Database::connection();

        $sql = "SELECT COUNT(*) as cnt FROM jtb_library_templates WHERE 1=1";
        $params = [];

        if (!empty($filters['category'])) {
            $sql .= " AND category_slug = ?";
            $params[] = $filters['category'];
        }

        if (!empty($filters['type'])) {
            $sql .= " AND template_type = ?";
            $params[] = $filters['type'];
        }

        if (isset($filters['is_premade'])) {
            $sql .= " AND is_premade = ?";
            $params[] = $filters['is_premade'] ? 1 : 0;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return (int)$row['cnt'];
    }

    /**
     * Search templates
     */
    public static function search(string $query, array $filters = []): array
    {
        $filters['search'] = $query;
        $filters['limit'] = $filters['limit'] ?? 50;
        return self::getAll($filters);
    }

    /**
     * Export template as JSON
     */
    public static function export(int $id): ?array
    {
        $template = self::get($id);
        if (!$template) {
            return null;
        }

        return [
            'jtb_template' => [
                'version' => '1.0',
                'name' => $template['name'],
                'description' => $template['description'],
                'category' => $template['category_slug'],
                'tags' => $template['tags'],
                'template_type' => $template['template_type'],
                'author' => $template['author'] ?? 'Jessie CMS'
            ],
            'content' => $template['content']
        ];
    }

    /**
     * Import template from JSON
     */
    public static function import(array $data): int|bool
    {
        // Validate structure
        if (!isset($data['jtb_template']) || !isset($data['content'])) {
            return false;
        }

        $meta = $data['jtb_template'];

        return self::save([
            'name' => $meta['name'] ?? 'Imported Template',
            'description' => $meta['description'] ?? null,
            'category_slug' => $meta['category'] ?? null,
            'tags' => $meta['tags'] ?? [],
            'content' => $data['content'],
            'template_type' => $meta['template_type'] ?? 'page',
            'author' => $meta['author'] ?? null,
            'is_premade' => 0, // Imported templates are user templates
            'is_featured' => 0
        ]);
    }

    /**
     * Create instance of template for inserting into page
     * Regenerates all IDs to avoid conflicts
     */
    public static function createInstance(int $id): ?array
    {
        $template = self::get($id);
        if (!$template) {
            return null;
        }

        // Increment download counter
        self::incrementDownloads($id);

        // Clone content with new IDs
        $content = $template['content'];
        if (isset($content['content']) && is_array($content['content'])) {
            $content['content'] = self::regenerateIds($content['content']);
        } else {
            $content = self::regenerateIds($content);
        }

        return $content;
    }

    /**
     * Regenerate IDs in content recursively
     */
    private static function regenerateIds(array $elements): array
    {
        if (!is_array($elements)) {
            return $elements;
        }

        // Check if this is a single element or array of elements
        if (isset($elements['type']) && isset($elements['id'])) {
            // Single element
            return self::regenerateElementId($elements);
        }

        // Array of elements
        foreach ($elements as $key => $element) {
            if (is_array($element)) {
                $elements[$key] = self::regenerateElementId($element);
            }
        }

        return $elements;
    }

    /**
     * Regenerate ID for a single element and its children
     */
    private static function regenerateElementId(array $element): array
    {
        if (isset($element['id'])) {
            $prefix = explode('_', $element['id'])[0] ?? 'el';
            $element['id'] = $prefix . '_' . self::generateRandomId();
        }

        if (isset($element['children']) && is_array($element['children'])) {
            foreach ($element['children'] as $key => $child) {
                if (is_array($child)) {
                    $element['children'][$key] = self::regenerateElementId($child);
                }
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
     * Generate URL-safe slug from name
     */
    private static function generateSlug(string $name): string
    {
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');

        // Add random suffix to ensure uniqueness
        $slug .= '-' . substr(self::generateRandomId(), 0, 4);

        return $slug;
    }

    /**
     * Check if tables exist
     */
    public static function tablesExist(): bool
    {
        $db = \core\Database::connection();

        try {
            $stmt = $db->query("SHOW TABLES LIKE 'jtb_library_templates'");
            return $stmt->fetch() !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
