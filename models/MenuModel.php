<?php
/**
 * MenuModel - Menu model for Jessie AI-CMS
 * 
 * @package JessieCMS
 * @since 2026-02-15
 */

require_once __DIR__ . '/BaseModel.php';

class MenuModel extends BaseModel
{
    protected static string $table = 'menus';

    /**
     * Find menu by location
     */
    public static function findByLocation(string $location): ?array
    {
        return static::findBy('location', $location);
    }

    /**
     * Get active menus
     */
    public static function active(string $orderBy = 'sort_order ASC', int $limit = 100): array
    {
        return static::all(['status' => 'active'], $orderBy, $limit);
    }

    /**
     * Get menu with its items
     */
    public static function withItems(int $menuId): ?array
    {
        // First get the menu
        $menu = static::find($menuId);
        if (!$menu) {
            return null;
        }

        // Get menu items
        $sql = "SELECT * FROM `menu_items` 
                WHERE menu_id = ? 
                ORDER BY sort_order ASC, id ASC";
        
        $stmt = static::db()->prepare($sql);
        $stmt->execute([$menuId]);
        
        $menu['items'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return $menu;
    }

    /**
     * Get menu by location with items
     */
    public static function getByLocationWithItems(string $location): ?array
    {
        $menu = static::findByLocation($location);
        if (!$menu) {
            return null;
        }

        return static::withItems($menu['id']);
    }

    /**
     * Get all menu locations
     */
    public static function getLocations(): array
    {
        $sql = "SELECT DISTINCT location FROM `menus` WHERE status = 'active'";
        $stmt = static::db()->prepare($sql);
        $stmt->execute();
        
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_column($result, 'location');
    }

    /**
     * Create menu item
     */
    public static function createItem(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        
        $sql = "INSERT INTO `menu_items` (`" . implode('`, `', $columns) . "`) VALUES ($placeholders)";
        $stmt = static::db()->prepare($sql);
        $stmt->execute(array_values($data));
        
        return (int)static::db()->lastInsertId();
    }

    /**
     * Update menu item
     */
    public static function updateItem(int $itemId, array $data): bool
    {
        if (empty($data)) {
            throw new \Exception('Data array cannot be empty');
        }

        $columns = array_keys($data);
        $setPairs = array_map(fn($col) => "`$col` = ?", $columns);
        
        $sql = "UPDATE `menu_items` SET " . implode(', ', $setPairs) . " WHERE id = ?";
        $params = array_values($data);
        $params[] = $itemId;
        
        $stmt = static::db()->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete menu item
     */
    public static function deleteItem(int $itemId): bool
    {
        $sql = "DELETE FROM `menu_items` WHERE id = ?";
        $stmt = static::db()->prepare($sql);
        $stmt->execute([$itemId]);
        
        return $stmt->rowCount() > 0;
    }
}