<?php
/**
 * Menu Helper Functions
 * 
 * Usage in themes:
 *   <?= render_menu('header') ?>
 *   <?= render_menu('main-navigation', ['class' => 'nav-menu', 'depth' => 2]) ?>
 */

/**
 * Render a menu by slug or location
 * 
 * @param string $slugOrLocation Menu slug or location (header, footer, sidebar)
 * @param array $options Rendering options:
 *   - class: CSS class for <ul> wrapper
 *   - id: HTML id for <ul> wrapper  
 *   - depth: Max nesting depth (default: 3)
 *   - item_class: CSS class for each <li>
 *   - link_class: CSS class for each <a>
 *   - show_description: Show item descriptions as title attr
 * @return string HTML output
 */
function render_menu(string $slugOrLocation, array $options = []): string
{
    $pdo = db();
    
    // Try to find menu by slug first, then by location
    $stmt = $pdo->prepare("
        SELECT * FROM menus 
        WHERE (slug = ? OR location = ?) 
          AND (is_active = 1 OR is_active IS NULL)
        LIMIT 1
    ");
    $stmt->execute([$slugOrLocation, $slugOrLocation]);
    $menu = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    if (!$menu) {
        return '<!-- Menu not found: ' . esc($slugOrLocation) . ' -->';
    }
    
    // Get menu items
    $stmt = $pdo->prepare("
        SELECT mi.*, p.slug as page_slug
        FROM menu_items mi
        LEFT JOIN pages p ON mi.page_id = p.id
        WHERE mi.menu_id = ? 
          AND (mi.is_active = 1 OR mi.is_active IS NULL)
        ORDER BY mi.parent_id IS NULL DESC, mi.parent_id ASC, mi.sort_order ASC
    ");
    $stmt->execute([$menu['id']]);
    $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
    if (empty($items)) {
        return '<!-- Menu empty: ' . esc($slugOrLocation) . ' -->';
    }
    
    // Filter by visibility
    $items = array_filter($items, function($item) {
        $visibility = $item['visibility'] ?? 'all';
        $isLoggedIn = function_exists('is_logged_in') ? is_logged_in() : isset($_SESSION['user_id']);
        $isAdmin = function_exists('is_admin') ? is_admin() : (($_SESSION['user_role'] ?? '') === 'admin');
        
        switch ($visibility) {
            case 'logged_in': return $isLoggedIn;
            case 'logged_out': return !$isLoggedIn;
            case 'admin': return $isAdmin;
            default: return true;
        }
    });
    
    // Build tree structure
    $tree = build_menu_tree($items);
    
    // Merge options with defaults
    $options = array_merge([
        'class' => 'menu',
        'id' => '',
        'depth' => $menu['max_depth'] ?? 3,
        'item_class' => '',
        'link_class' => '',
        'show_description' => true,
        'dropdown_class' => 'submenu',
    ], $options);
    
    return render_menu_tree($tree, $options, 0);
}

/**
 * Build hierarchical tree from flat menu items
 */
function build_menu_tree(array $items, ?int $parentId = null): array
{
    $tree = [];
    foreach ($items as $item) {
        if ($item['parent_id'] == $parentId) {
            $item['children'] = build_menu_tree($items, $item['id']);
            $tree[] = $item;
        }
    }
    return $tree;
}

/**
 * Render menu tree as HTML
 */
function render_menu_tree(array $items, array $options, int $level): string
{
    if (empty($items) || $level >= $options['depth']) {
        return '';
    }
    
    $ulClass = $level === 0 ? $options['class'] : $options['dropdown_class'];
    $ulId = $level === 0 && $options['id'] ? ' id="' . esc($options['id']) . '"' : '';
    
    $html = '<ul class="' . esc($ulClass) . '"' . $ulId . '>' . "\n";
    
    foreach ($items as $item) {
        $hasChildren = !empty($item['children']);
        $liClasses = array_filter([
            $options['item_class'],
            $hasChildren ? 'has-submenu' : '',
            is_menu_item_active($item) ? 'active' : '',
        ]);
        $liClass = $liClasses ? ' class="' . esc(implode(' ', $liClasses)) . '"' : '';
        
        // Build URL
        $url = '#';
        if ($item['page_slug']) {
            $url = '/page/' . $item['page_slug'];
        } elseif ($item['url']) {
            $url = $item['url'];
        }
        
        // Build link attributes
        $linkAttrs = [];
        $linkAttrs[] = 'href="' . esc($url) . '"';
        
        if ($options['link_class']) {
            $linkAttrs[] = 'class="' . esc($options['link_class']) . '"';
        }
        
        if ($item['open_in_new_tab'] ?? ($item['target'] === '_blank')) {
            $linkAttrs[] = 'target="_blank"';
            $linkAttrs[] = 'rel="noopener"';
        }
        
        if ($options['show_description'] && !empty($item['description'])) {
            $linkAttrs[] = 'title="' . esc($item['description']) . '"';
        }
        
        if (!empty($item['css_class'])) {
            $linkAttrs[] = 'class="' . esc($item['css_class']) . '"';
        }
        
        $html .= '  <li' . $liClass . '>';
        $html .= '<a ' . implode(' ', $linkAttrs) . '>';
        
        if (!empty($item['icon'])) {
            $html .= '<span class="menu-icon">' . esc($item['icon']) . '</span> ';
        }
        
        $html .= esc($item['title']);
        $html .= '</a>';
        
        // Render children
        if ($hasChildren) {
            $html .= "\n" . render_menu_tree($item['children'], $options, $level + 1);
        }
        
        $html .= "</li>\n";
    }
    
    $html .= '</ul>';
    
    return $html;
}

/**
 * Check if menu item matches current URL
 */
function is_menu_item_active(array $item): bool
{
    $currentPath = $_SERVER['REQUEST_URI'] ?? '/';
    $currentPath = parse_url($currentPath, PHP_URL_PATH);
    
    if ($item['page_slug']) {
        return $currentPath === '/page/' . $item['page_slug'];
    }
    
    if ($item['url']) {
        $itemPath = parse_url($item['url'], PHP_URL_PATH);
        return $currentPath === $itemPath;
    }
    
    return false;
}

/**
 * Get menu by slug (returns array or null)
 */
function get_menu(string $slug): ?array
{
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM menus WHERE slug = ? AND (is_active = 1 OR is_active IS NULL)");
    $stmt->execute([$slug]);
    return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
}

/**
 * Get menu items as flat array
 */
function get_menu_items(string $slug): array
{
    $menu = get_menu($slug);
    if (!$menu) return [];
    
    $pdo = db();
    $stmt = $pdo->prepare("
        SELECT mi.*, p.slug as page_slug
        FROM menu_items mi
        LEFT JOIN pages p ON mi.page_id = p.id
        WHERE mi.menu_id = ? AND (mi.is_active = 1 OR mi.is_active IS NULL)
        ORDER BY mi.sort_order ASC
    ");
    $stmt->execute([$menu['id']]);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}
