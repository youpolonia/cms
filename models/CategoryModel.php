<?php
/**
 * CategoryModel - Article category model for Jessie AI-CMS
 * 
 * @package JessieCMS
 * @since 2026-02-15
 */

require_once __DIR__ . '/BaseModel.php';

class CategoryModel extends BaseModel
{
    protected static string $table = 'article_categories';

    /**
     * Find category by slug
     */
    public static function findBySlug(string $slug): ?array
    {
        return static::findBy('slug', $slug);
    }

    /**
     * Get category with article count
     */
    public static function withArticleCount(int $id): ?array
    {
        $sql = "SELECT c.*, 
                       COUNT(a.id) as article_count,
                       COUNT(CASE WHEN a.status = 'published' THEN 1 END) as published_count
                FROM `article_categories` c 
                LEFT JOIN `articles` a ON c.id = a.category_id 
                WHERE c.id = ? 
                GROUP BY c.id 
                LIMIT 1";
        
        $stmt = static::db()->prepare($sql);
        $stmt->execute([$id]);
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Get all categories with article counts
     */
    public static function allWithCounts(string $orderBy = 'name ASC', int $limit = 100): array
    {
        $sql = "SELECT c.*, 
                       COUNT(a.id) as article_count,
                       COUNT(CASE WHEN a.status = 'published' THEN 1 END) as published_count
                FROM `article_categories` c 
                LEFT JOIN `articles` a ON c.id = a.category_id 
                GROUP BY c.id 
                ORDER BY $orderBy 
                LIMIT ?";
        
        $stmt = static::db()->prepare($sql);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get categories with published articles only
     */
    public static function withPublishedArticles(string $orderBy = 'name ASC', int $limit = 100): array
    {
        $sql = "SELECT DISTINCT c.* 
                FROM `article_categories` c 
                INNER JOIN `articles` a ON c.id = a.category_id 
                WHERE a.status = 'published' 
                ORDER BY $orderBy 
                LIMIT ?";
        
        $stmt = static::db()->prepare($sql);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get category hierarchy (if parent_id exists)
     */
    public static function getHierarchy(): array
    {
        $sql = "SELECT * FROM `article_categories` ORDER BY parent_id ASC, name ASC";
        $stmt = static::db()->prepare($sql);
        $stmt->execute();
        
        $categories = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Build hierarchy if parent_id column exists
        $hierarchy = [];
        $indexed = [];
        
        // Index all categories
        foreach ($categories as $category) {
            $indexed[$category['id']] = $category;
            $indexed[$category['id']]['children'] = [];
        }
        
        // Build tree
        foreach ($categories as $category) {
            if (isset($category['parent_id']) && $category['parent_id'] && isset($indexed[$category['parent_id']])) {
                $indexed[$category['parent_id']]['children'][] = &$indexed[$category['id']];
            } else {
                $hierarchy[] = &$indexed[$category['id']];
            }
        }
        
        return $hierarchy;
    }

    /**
     * Get top-level categories (no parent)
     */
    public static function topLevel(string $orderBy = 'name ASC', int $limit = 100): array
    {
        // Check if parent_id column exists, if not return all
        $sql = "SHOW COLUMNS FROM `article_categories` LIKE 'parent_id'";
        $stmt = static::db()->prepare($sql);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            // No parent_id column, return all categories
            return static::all([], $orderBy, $limit);
        }
        
        return static::all(['parent_id' => null], $orderBy, $limit);
    }
}