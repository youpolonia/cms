<?php
/**
 * Admin Menu Renderer
 * Renders centralized menu for both legacy and MVC layouts
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(dirname(__DIR__)));
}

/**
 * Get admin menu configuration
 */
function getAdminMenu(): array {
    static $menu = null;
    if ($menu === null) {
        $menuFile = __DIR__ . '/admin_menu.php';
        $menu = file_exists($menuFile) ? require $menuFile : [];
    }
    return $menu;
}

/**
 * Check if current path matches menu item
 */
function isMenuActive(string $url): string {
    $currentPath = strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/';
    
    if ($url === '/admin' && ($currentPath === '/admin' || $currentPath === '/admin/' || $currentPath === '/admin/dashboard')) {
        return 'active';
    }
    
    return ($url !== '/admin' && strpos($currentPath, $url) === 0) ? 'active' : '';
}

/**
 * Render admin navigation HTML
 */
function renderAdminNav(): string {
    $menu = getAdminMenu();
    $html = '';
    
    foreach ($menu as $key => $item) {
        if ($key === 'user') continue; // User menu rendered separately
        
        $type = $item['type'] ?? 'link';
        
        if ($type === 'link') {
            // Simple link
            $active = isMenuActive($item['url'] ?? '#');
            $html .= '<a href="' . htmlspecialchars($item['url'] ?? '#') . '" class="nav-link ' . $active . '">' . ($item['label'] ?? '') . '</a>' . "\n";
        } elseif ($type === 'mega') {
            // Mega-menu with sections
            $badge = isset($item['badge']) ? ' <span class="nav-badge">' . $item['badge'] . '</span>' : '';
            $cols = (int)($item['columns'] ?? 3);
            $html .= '<div class="nav-dropdown nav-mega">' . "\n";
            $html .= '    <span class="nav-link">' . ($item['label'] ?? '') . ' ▾' . $badge . '</span>' . "\n";
            $html .= '    <div class="nav-dropdown-menu nav-mega-menu" style="display:none;min-width:' . ($cols * 200) . 'px;">' . "\n";
            $html .= '        <div style="display:grid;grid-template-columns:repeat(' . $cols . ',1fr);gap:16px;padding:12px;">' . "\n";
            
            foreach ($item['sections'] ?? [] as $section) {
                $html .= '            <div>' . "\n";
                if (!empty($section['title'])) {
                    $html .= '                <div style="font-weight:600;font-size:13px;color:#94a3b8;margin-bottom:8px;padding:4px 8px;">' . htmlspecialchars($section['title']) . '</div>' . "\n";
                }
                foreach ($section['items'] ?? [] as $subItem) {
                    $html .= '                <a href="' . htmlspecialchars($subItem['url'] ?? '#') . '" class="nav-dropdown-item">' . ($subItem['label'] ?? '') . '</a>' . "\n";
                }
                $html .= '            </div>' . "\n";
            }
            
            $html .= '        </div>' . "\n";
            $html .= '    </div>' . "\n";
            $html .= '</div>' . "\n";
        } else {
            // Simple dropdown
            $badge = isset($item['badge']) ? ' <span class="nav-badge">' . $item['badge'] . '</span>' : '';
            $html .= '<div class="nav-dropdown">' . "\n";
            $html .= '    <span class="nav-link">' . ($item['label'] ?? '') . ' ▾' . $badge . '</span>' . "\n";
            $html .= '    <div class="nav-dropdown-menu">' . "\n";
            
            foreach ($item['items'] ?? [] as $subItem) {
                $html .= '        <a href="' . htmlspecialchars($subItem['url'] ?? '#') . '" class="nav-dropdown-item">' . ($subItem['label'] ?? '') . '</a>' . "\n";
            }
            
            $html .= '    </div>' . "\n";
            $html .= '</div>' . "\n";
        }
    }
    
    return $html;
}

/**
 * Render user dropdown menu HTML
 */
function renderUserNav(string $username = 'Admin'): string {
    $menu = getAdminMenu();
    $userMenu = $menu['user'] ?? null;
    
    if (!$userMenu) {
        return '';
    }
    
    $html = '<div class="nav-dropdown">' . "\n";
    $html .= '    <span class="nav-link">👤 ' . htmlspecialchars($username) . ' ▾</span>' . "\n";
    $html .= '    <div class="nav-dropdown-menu">' . "\n";
    
    foreach ($userMenu['items'] ?? [] as $item) {
        $html .= '        <a href="' . htmlspecialchars($item['url']) . '" class="nav-dropdown-item">' . $item['label'] . '</a>' . "\n";
    }
    
    $html .= '    </div>' . "\n";
    $html .= '</div>' . "\n";
    
    return $html;
}
