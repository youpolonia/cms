<?php
declare(strict_types=1);

namespace Api;

require_once __DIR__ . '/../../../core/api_middleware.php';

use Core\Request;

/**
 * Menus API Controller
 * 
 * Handles public read-only API requests for menus and navigation
 * 
 * @package JessieCMS
 * @since 2026-02-15
 */
class MenusApiController {
    
    public function __construct() {
        // Apply rate limiting to all methods
        \Core\api_rate_limit(60);
    }
    
    /**
     * GET /api/v1/menus
     * List all active menus
     */
    public function index(?Request $request = null): void {
        try {
            $pdo = \core\Database::connection();
            
            $sql = "
                SELECT 
                    id,
                    name,
                    location,
                    description,
                    is_active,
                    created_at,
                    updated_at
                FROM menus 
                WHERE is_active = 1
                ORDER BY name ASC
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $menus = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Format menus
            $formattedMenus = array_map(function($menu) {
                return [
                    'id' => (int)$menu['id'],
                    'name' => $menu['name'],
                    'location' => $menu['location'],
                    'description' => $menu['description'] ?? '',
                    'is_active' => (bool)$menu['is_active'],
                    'created_at' => $menu['created_at'],
                    'updated_at' => $menu['updated_at']
                ];
            }, $menus);
            
            \Core\api_json_response([
                'data' => $formattedMenus
            ]);
            
        } catch (\Throwable $e) {
            error_log("Menus API error: " . $e->getMessage());
            \Core\api_error('Failed to fetch menus', 500);
        }
    }
    
    /**
     * GET /api/v1/menus/{location}
     * Get menu with items by location (header, footer, etc.)
     */
    public function show(?Request $request = null): void {
        $location = $request?->getParam('location') ?? $_GET['location'] ?? null;
        
        if (!$location) {
            \Core\api_error('Menu location is required', 400);
        }
        
        try {
            $pdo = \core\Database::connection();
            
            // Get menu by location
            $menuSql = "
                SELECT 
                    id,
                    name,
                    location,
                    description,
                    is_active,
                    created_at,
                    updated_at
                FROM menus 
                WHERE location = ? AND is_active = 1
                LIMIT 1
            ";
            
            $stmt = $pdo->prepare($menuSql);
            $stmt->execute([$location]);
            $menu = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$menu) {
                \Core\api_error('Menu not found', 404);
            }
            
            // Get menu items
            $itemsSql = "
                SELECT 
                    id,
                    title,
                    url,
                    target,
                    icon,
                    parent_id,
                    sort_order,
                    is_active
                FROM menu_items 
                WHERE menu_id = ? AND is_active = 1
                ORDER BY parent_id ASC, sort_order ASC
            ";
            
            $stmt = $pdo->prepare($itemsSql);
            $stmt->execute([$menu['id']]);
            $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Build hierarchical menu structure
            $menuTree = $this->buildMenuTree($items);
            
            // Format menu with items
            $formattedMenu = [
                'id' => (int)$menu['id'],
                'name' => $menu['name'],
                'location' => $menu['location'],
                'description' => $menu['description'] ?? '',
                'is_active' => (bool)$menu['is_active'],
                'items' => $menuTree,
                'created_at' => $menu['created_at'],
                'updated_at' => $menu['updated_at']
            ];
            
            \Core\api_json_response([
                'data' => $formattedMenu
            ]);
            
        } catch (\Throwable $e) {
            error_log("Menu API error: " . $e->getMessage());
            \Core\api_error('Failed to fetch menu', 500);
        }
    }
    
    /**
     * Build hierarchical menu tree from flat array
     * 
     * @param array $items Flat array of menu items
     * @param int|null $parentId Parent ID to build tree for
     * @return array Hierarchical menu structure
     */
    private function buildMenuTree(array $items, ?int $parentId = null): array {
        $tree = [];
        
        foreach ($items as $item) {
            $itemParentId = $item['parent_id'] ? (int)$item['parent_id'] : null;
            if ($itemParentId === $parentId) {
                $formattedItem = [
                    'id' => (int)$item['id'],
                    'title' => $item['title'],
                    'url' => $item['url'],
                    'target' => $item['target'] ?? '_self',
                    'icon' => $item['icon'] ?? null,
                    'sort_order' => (int)$item['sort_order'],
                    'is_active' => (bool)$item['is_active'],
                    'children' => []
                ];
                
                // Recursively get children
                $children = $this->buildMenuTree($items, (int)$item['id']);
                if (!empty($children)) {
                    $formattedItem['children'] = $children;
                }
                
                $tree[] = $formattedItem;
            }
        }
        
        return $tree;
    }
}