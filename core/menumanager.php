<?php
/**
 * MenuManager - Handles menu loading, rendering and management
 */

require_once __DIR__ . '/database.php';
class MenuManager {
    private static $instance;
    private $db;
    private $menuCache = [];

    private function __construct() {
        $this->db = \core\Database::connection();
        require_once __DIR__.'/../includes/session.php';
        cms_session_start();
    }

    public static function getInstance(): self {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load menu items from database
     */
    public function loadMenu(string $menuName, string $area = 'default'): array {
        $this->validateSession();

        $stmt = $this->db->prepare("
            SELECT * FROM menus 
            WHERE name = :name AND area = :area 
            ORDER BY parent_id, sort_order ASC
        ");
        $stmt->execute([':name' => $menuName, ':area' => $area]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->menuCache[$menuName][$area] = $this->buildMenuTree($items);
        return $this->menuCache[$menuName][$area];
    }

    /**
     * Build hierarchical menu structure
     */
    private function buildMenuTree(array $items, $parentId = 0): array {
        $branch = [];
        foreach ($items as $item) {
            if ($item['parent_id'] == $parentId) {
                $children = $this->buildMenuTree($items, $item['id']);
                if ($children) {
                    $item['children'] = $children;
                }
                $branch[] = $item;
            }
        }
        return $branch;
    }

    /**
     * Render menu as HTML
     */
    public function renderMenu(string $menuName, string $area = 'default'): string {
        $items = $this->getMenuItems($menuName, $area);
        return $this->generateHtml($items);
    }

    /**
     * Get cached menu items
     */
    public function getMenuItems(string $menuName, string $area = 'default'): array {
        if (!isset($this->menuCache[$menuName][$area])) {
            return $this->loadMenu($menuName, $area);
        }
        return $this->menuCache[$menuName][$area];
    }

    /**
     * Generate HTML from menu items
     */
    private function generateHtml(array $items, $level = 0): string {
        $html = $level === 0 ? '<ul class="menu">' : '<ul class="submenu">';
        foreach ($items as $item) {
            $html .= '<li>';
            $html .= '<a href="' . htmlspecialchars($item['url']) . '">';
            $html .= htmlspecialchars($item['title']);
            $html .= '</a>';
            if (!empty($item['children'])) {
                $html .= $this->generateHtml($item['children'], $level + 1);
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * Validate session and CSRF token
     */
    private function validateSession(): void {
        if (session_status() !== PHP_SESSION_ACTIVE || !function_exists('cms_session_start')) {
            throw new RuntimeException('Session not properly initialized');
        }
        
        if (empty($_SESSION['csrf_token'])) {
            throw new RuntimeException('CSRF token missing');
        }
    }
}