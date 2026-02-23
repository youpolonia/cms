<?php
/**
 * ArticleModel - Article model for Jessie AI-CMS
 * 
 * @package JessieCMS
 * @since 2026-02-15
 */

require_once __DIR__ . '/BaseModel.php';

class ArticleModel extends BaseModel
{
    protected static string $table = 'articles';

    /**
     * Find article by slug
     */
    public static function findBySlug(string $slug): ?array
    {
        return static::findBy('slug', $slug);
    }

    /**
     * Get published articles
     */
    public static function published(array $where = [], string $orderBy = 'published_at DESC', int $limit = 100): array
    {
        $where['status'] = 'published';
        return static::all($where, $orderBy, $limit);
    }

    /**
     * Get article with category information
     */
    public static function withCategory(int $id): ?array
    {
        $sql = "SELECT a.*, c.name as category_name, c.slug as category_slug 
                FROM `articles` a 
                LEFT JOIN `article_categories` c ON a.category_id = c.id 
                WHERE a.id = ? 
                LIMIT 1";
        
        $stmt = static::db()->prepare($sql);
        $stmt->execute([$id]);
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Get articles by category ID
     */
    public static function byCategory(int $categoryId, string $orderBy = 'published_at DESC', int $limit = 100): array
    {
        return static::all(['category_id' => $categoryId], $orderBy, $limit);
    }

    /**
     * Search articles by title or content
     */
    public static function search(string $query, int $limit = 50): array
    {
        $sql = "SELECT * FROM `articles` 
                WHERE (title LIKE ? OR content LIKE ?) 
                AND status = 'published' 
                ORDER BY published_at DESC 
                LIMIT ?";
        
        $searchTerm = '%' . $query . '%';
        $stmt = static::db()->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $limit]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}