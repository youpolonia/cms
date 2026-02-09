<?php
/**
 * Template Conditions Manager
 * Manages conditions for when templates should be applied
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Template_Conditions
{
    /**
     * Condition types
     */
    const TYPE_INCLUDE = 'include';
    const TYPE_EXCLUDE = 'exclude';

    /**
     * Page types
     */
    const PAGE_ALL = 'all';
    const PAGE_HOMEPAGE = 'homepage';
    const PAGE_SINGLE_POST = 'single_post';
    const PAGE_SINGLE_PAGE = 'single_page';
    const PAGE_ARCHIVE = 'archive';
    const PAGE_CATEGORY = 'category';
    const PAGE_TAG = 'tag';
    const PAGE_404 = '404';
    const PAGE_SEARCH = 'search';
    const PAGE_AUTHOR = 'author';

    /**
     * Get all page types with labels
     */
    public static function getPageTypes(): array
    {
        return [
            self::PAGE_ALL => [
                'label' => 'All Pages',
                'has_objects' => false,
                'priority' => 1
            ],
            self::PAGE_HOMEPAGE => [
                'label' => 'Homepage',
                'has_objects' => false,
                'priority' => 10
            ],
            self::PAGE_SINGLE_POST => [
                'label' => 'Single Post',
                'has_objects' => true,
                'object_label' => 'Select Post',
                'priority' => 20
            ],
            self::PAGE_SINGLE_PAGE => [
                'label' => 'Single Page',
                'has_objects' => true,
                'object_label' => 'Select Page',
                'priority' => 20
            ],
            self::PAGE_ARCHIVE => [
                'label' => 'Archive Pages',
                'has_objects' => false,
                'priority' => 5
            ],
            self::PAGE_CATEGORY => [
                'label' => 'Category Archive',
                'has_objects' => true,
                'object_label' => 'Select Category',
                'priority' => 15
            ],
            self::PAGE_TAG => [
                'label' => 'Tag Archive',
                'has_objects' => true,
                'object_label' => 'Select Tag',
                'priority' => 15
            ],
            self::PAGE_404 => [
                'label' => '404 Error Page',
                'has_objects' => false,
                'priority' => 50
            ],
            self::PAGE_SEARCH => [
                'label' => 'Search Results',
                'has_objects' => false,
                'priority' => 50
            ],
            self::PAGE_AUTHOR => [
                'label' => 'Author Archive',
                'has_objects' => true,
                'object_label' => 'Select Author',
                'priority' => 15
            ]
        ];
    }

    /**
     * Get all conditions for a template
     */
    public static function getForTemplate(int $templateId): array
    {
        $db = \core\Database::connection();

        $stmt = $db->prepare("
            SELECT id, template_id, condition_type, page_type, object_id, created_at
            FROM jtb_template_conditions
            WHERE template_id = ?
            ORDER BY condition_type ASC, page_type ASC
        ");
        $stmt->execute([$templateId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get include conditions for a template
     */
    public static function getIncludeConditions(int $templateId): array
    {
        $db = \core\Database::connection();

        $stmt = $db->prepare("
            SELECT id, template_id, condition_type, page_type, object_id, created_at
            FROM jtb_template_conditions
            WHERE template_id = ? AND condition_type = 'include'
            ORDER BY page_type ASC
        ");
        $stmt->execute([$templateId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get exclude conditions for a template
     */
    public static function getExcludeConditions(int $templateId): array
    {
        $db = \core\Database::connection();

        $stmt = $db->prepare("
            SELECT id, template_id, condition_type, page_type, object_id, created_at
            FROM jtb_template_conditions
            WHERE template_id = ? AND condition_type = 'exclude'
            ORDER BY page_type ASC
        ");
        $stmt->execute([$templateId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Add a condition to a template
     *
     * @param int $templateId Template ID
     * @param string $conditionType 'include' or 'exclude'
     * @param string $pageType Page type (homepage, single_post, etc.)
     * @param int|null $objectId Optional specific object ID
     * @return int|bool Condition ID on success, false on failure
     */
    public static function add(int $templateId, string $conditionType, string $pageType, ?int $objectId = null): int|bool
    {
        // Validate condition type
        if (!in_array($conditionType, [self::TYPE_INCLUDE, self::TYPE_EXCLUDE])) {
            return false;
        }

        // Validate page type
        $validPageTypes = array_keys(self::getPageTypes());
        if (!in_array($pageType, $validPageTypes)) {
            return false;
        }

        // Check if condition already exists
        if (self::exists($templateId, $conditionType, $pageType, $objectId)) {
            return false;
        }

        $db = \core\Database::connection();

        $stmt = $db->prepare("
            INSERT INTO jtb_template_conditions (template_id, condition_type, page_type, object_id, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");

        $success = $stmt->execute([
            $templateId,
            $conditionType,
            $pageType,
            $objectId
        ]);

        return $success ? (int) $db->lastInsertId() : false;
    }

    /**
     * Remove a condition by ID
     */
    public static function remove(int $conditionId): bool
    {
        $db = \core\Database::connection();

        $stmt = $db->prepare("DELETE FROM jtb_template_conditions WHERE id = ?");
        return $stmt->execute([$conditionId]);
    }

    /**
     * Remove all conditions for a template
     */
    public static function clearForTemplate(int $templateId): bool
    {
        $db = \core\Database::connection();

        $stmt = $db->prepare("DELETE FROM jtb_template_conditions WHERE template_id = ?");
        return $stmt->execute([$templateId]);
    }

    /**
     * Bulk set conditions (replaces existing)
     *
     * @param int $templateId Template ID
     * @param array $conditions Array of conditions: [['type' => 'include', 'page_type' => 'homepage', 'object_id' => null], ...]
     */
    public static function setForTemplate(int $templateId, array $conditions): bool
    {
        // Clear existing conditions
        self::clearForTemplate($templateId);

        // Add new conditions
        foreach ($conditions as $condition) {
            $type = $condition['type'] ?? $condition['condition_type'] ?? self::TYPE_INCLUDE;
            $pageType = $condition['page_type'] ?? '';
            $objectId = $condition['object_id'] ?? null;

            if (!empty($pageType)) {
                self::add($templateId, $type, $pageType, $objectId);
            }
        }

        return true;
    }

    /**
     * Check if a condition exists
     */
    public static function exists(int $templateId, string $conditionType, string $pageType, ?int $objectId = null): bool
    {
        $db = \core\Database::connection();

        if ($objectId === null) {
            $stmt = $db->prepare("
                SELECT id FROM jtb_template_conditions
                WHERE template_id = ? AND condition_type = ? AND page_type = ? AND object_id IS NULL
            ");
            $stmt->execute([$templateId, $conditionType, $pageType]);
        } else {
            $stmt = $db->prepare("
                SELECT id FROM jtb_template_conditions
                WHERE template_id = ? AND condition_type = ? AND page_type = ? AND object_id = ?
            ");
            $stmt->execute([$templateId, $conditionType, $pageType, $objectId]);
        }

        return $stmt->fetch() !== false;
    }

    /**
     * Get available objects for a page type
     * Updated 2026-02-04: Fixed table names for Jessie CMS (articles instead of posts, etc.)
     */
    public static function getObjectsForType(string $pageType): array
    {
        $db = \core\Database::connection();
        $objects = [];

        try {
            switch ($pageType) {
                case self::PAGE_SINGLE_POST:
                    // Get all articles (posts) - Jessie CMS uses 'articles' table
                    $stmt = $db->query("
                        SELECT id, title as name
                        FROM articles
                        WHERE status = 'published'
                        ORDER BY title ASC
                        LIMIT 100
                    ");
                    $objects = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    break;

                case self::PAGE_SINGLE_PAGE:
                    // Get all pages - Jessie CMS uses 'pages' table
                    $stmt = $db->query("
                        SELECT id, title as name
                        FROM pages
                        WHERE status = 'published'
                        ORDER BY title ASC
                        LIMIT 100
                    ");
                    $objects = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    break;

                case self::PAGE_CATEGORY:
                    // Get all categories
                    $stmt = $db->query("
                        SELECT id, name
                        FROM categories
                        ORDER BY name ASC
                        LIMIT 100
                    ");
                    $objects = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    break;

                case self::PAGE_TAG:
                    // Get all tags from article_categories (Jessie CMS uses this for tags too)
                    // or check if tags table exists
                    try {
                        $stmt = $db->query("
                            SELECT id, name
                            FROM tags
                            ORDER BY name ASC
                            LIMIT 100
                        ");
                        $objects = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    } catch (\PDOException $e) {
                        // Tags table doesn't exist, return empty
                        $objects = [];
                    }
                    break;

                case self::PAGE_AUTHOR:
                    // Get all authors/users - Jessie CMS uses 'users' table
                    $stmt = $db->query("
                        SELECT id, username as name
                        FROM users
                        WHERE status = 'active'
                        ORDER BY username ASC
                        LIMIT 100
                    ");
                    $objects = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    break;
            }
        } catch (\PDOException $e) {
            // Log error but return empty array
            error_log('JTB Conditions: Error fetching objects for type ' . $pageType . ': ' . $e->getMessage());
            $objects = [];
        }

        return $objects;
    }

    /**
     * Get human-readable label for a condition
     */
    public static function getConditionLabel(array $condition): string
    {
        $pageTypes = self::getPageTypes();
        $pageType = $condition['page_type'] ?? '';
        $objectId = $condition['object_id'] ?? null;

        $label = $pageTypes[$pageType]['label'] ?? $pageType;

        if ($objectId !== null) {
            $objectName = self::getObjectName($pageType, $objectId);
            if ($objectName) {
                $label .= ': ' . $objectName;
            } else {
                $label .= ' #' . $objectId;
            }
        }

        return $label;
    }

    /**
     * Get object name by type and ID
     * Updated 2026-02-04: Fixed table names for Jessie CMS
     */
    private static function getObjectName(string $pageType, int $objectId): ?string
    {
        $db = \core\Database::connection();

        try {
            switch ($pageType) {
                case self::PAGE_SINGLE_POST:
                    // Jessie CMS uses 'articles' table for posts
                    $stmt = $db->prepare("SELECT title FROM articles WHERE id = ?");
                    $stmt->execute([$objectId]);
                    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                    return $row ? $row['title'] : null;

                case self::PAGE_SINGLE_PAGE:
                    // Jessie CMS uses 'pages' table
                    $stmt = $db->prepare("SELECT title FROM pages WHERE id = ?");
                    $stmt->execute([$objectId]);
                    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                    return $row ? $row['title'] : null;

                case self::PAGE_CATEGORY:
                    $stmt = $db->prepare("SELECT name FROM categories WHERE id = ?");
                    $stmt->execute([$objectId]);
                    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                    return $row ? $row['name'] : null;

                case self::PAGE_TAG:
                    try {
                        $stmt = $db->prepare("SELECT name FROM tags WHERE id = ?");
                        $stmt->execute([$objectId]);
                        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                        return $row ? $row['name'] : null;
                    } catch (\PDOException $e) {
                        return null;
                    }

                case self::PAGE_AUTHOR:
                    $stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
                    $stmt->execute([$objectId]);
                    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                    return $row ? $row['username'] : null;
            }
        } catch (\PDOException $e) {
            error_log('JTB Conditions: Error fetching object name: ' . $e->getMessage());
            return null;
        }

        return null;
    }

    /**
     * Get priority for a condition (more specific = higher priority)
     */
    public static function getPriority(array $condition): int
    {
        $pageTypes = self::getPageTypes();
        $pageType = $condition['page_type'] ?? self::PAGE_ALL;
        $objectId = $condition['object_id'] ?? null;

        $basePriority = $pageTypes[$pageType]['priority'] ?? 1;

        // Specific object has higher priority
        if ($objectId !== null) {
            $basePriority += 100;
        }

        return $basePriority;
    }
}
