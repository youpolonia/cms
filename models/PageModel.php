<?php
/**
 * PageModel - Page model for Jessie AI-CMS
 * 
 * @package JessieCMS
 * @since 2026-02-15
 */

require_once __DIR__ . '/BaseModel.php';

class PageModel extends BaseModel
{
    protected static string $table = 'pages';

    /**
     * Find page by slug
     */
    public static function findBySlug(string $slug): ?array
    {
        return static::findBy('slug', $slug);
    }

    /**
     * Get published pages
     */
    public static function published(array $where = [], string $orderBy = 'created_at DESC', int $limit = 100): array
    {
        $where['status'] = 'published';
        return static::all($where, $orderBy, $limit);
    }

    /**
     * Get pages by template
     */
    public static function byTemplate(string $template, string $orderBy = 'created_at DESC', int $limit = 100): array
    {
        return static::all(['template' => $template], $orderBy, $limit);
    }

    /**
     * Get pages in menu
     */
    public static function inMenu(string $orderBy = 'menu_order ASC', int $limit = 100): array
    {
        return static::all(['show_in_menu' => 1], $orderBy, $limit);
    }

    /**
     * Search pages by title or content
     */
    public static function search(string $query, int $limit = 50): array
    {
        $sql = "SELECT * FROM `pages` 
                WHERE (title LIKE ? OR content LIKE ?) 
                AND status = 'published' 
                ORDER BY created_at DESC 
                LIMIT ?";
        
        $searchTerm = '%' . $query . '%';
        $stmt = static::db()->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $limit]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}