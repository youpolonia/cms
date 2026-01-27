<?php
/**
 * Plugin Menu Loader
 * Automatically loads menu items from active plugins
 */

declare(strict_types=1);

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

/**
 * Get menu items from all active plugins
 */
function get_plugin_menu_items(): array
{
    $installedFile = CMS_ROOT . '/config/installed_plugins.json';
    $pluginsDir = CMS_ROOT . '/plugins';
    
    if (!file_exists($installedFile)) {
        return [];
    }
    
    $installed = json_decode(file_get_contents($installedFile), true) ?: [];
    $menuItems = [];
    
    foreach ($installed as $slug => $info) {
        if (!($info['active'] ?? false)) {
            continue;
        }
        
        $pluginJson = $pluginsDir . '/' . $slug . '/plugin.json';
        if (!file_exists($pluginJson)) {
            continue;
        }
        
        $meta = json_decode(file_get_contents($pluginJson), true);
        if (!$meta || empty($meta['menu'])) {
            continue;
        }
        
        $menu = $meta['menu'];
        $section = $menu['section'] ?? 'plugins';
        
        if (!isset($menuItems[$section])) {
            $menuItems[$section] = [];
        }
        
        foreach ($menu['items'] ?? [] as $item) {
            $menuItems[$section][] = [
                'title' => $item['title'] ?? $meta['name'] ?? $slug,
                'icon' => $item['icon'] ?? '🧩',
                'url' => $item['url'] ?? '/admin/plugins/' . $slug,
                'badge' => $item['badge'] ?? null,
                'plugin' => $slug
            ];
        }
    }
    
    return $menuItems;
}

/**
 * Render plugin menu items for a specific section
 */
function render_plugin_menu(string $section): string
{
    $items = get_plugin_menu_items();
    
    if (empty($items[$section])) {
        return '';
    }
    
    $html = '';
    $currentPath = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
    
    foreach ($items[$section] as $item) {
        $isActive = strpos($currentPath, $item['url']) === 0 ? 'active' : '';
        $badge = $item['badge'] ? '<span class="nav-badge">' . htmlspecialchars($item['badge']) . '</span>' : '';
        
        $html .= sprintf(
            '<a href="%s" class="nav-link %s">%s %s %s</a>' . "\n",
            htmlspecialchars($item['url']),
            $isActive,
            $item['icon'],
            htmlspecialchars($item['title']),
            $badge
        );
    }
    
    return $html;
}
