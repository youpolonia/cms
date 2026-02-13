<?php
/**
 * White-Label Helper
 * Loads WL settings and provides helper functions
 */

if (!function_exists('wl')) {
    /**
     * Get a white-label setting
     * @param string $key Setting key without wl_ prefix
     * @param string $default Default value
     */
    function wl(string $key, string $default = ''): string
    {
        static $cache = null;
        if ($cache === null) {
            try {
                $pdo = \Core\Database::connection();
                $rows = $pdo->query("SELECT `key`, `value` FROM settings WHERE `key` LIKE 'wl_%'")->fetchAll(\PDO::FETCH_KEY_PAIR);
                $cache = $rows;
            } catch (\Throwable $e) {
                $cache = [];
            }
        }
        $fullKey = "wl_{$key}";
        $val = $cache[$fullKey] ?? '';
        return $val !== '' ? $val : $default;
    }
}

if (!function_exists('wl_admin_title')) {
    function wl_admin_title(string $pageTitle = 'Admin'): string
    {
        $name = wl('admin_name', 'Jessie AI-CMS');
        return htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') . ' - ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('wl_sidebar_brand')) {
    function wl_sidebar_brand(): string
    {
        $logo = wl('admin_logo');
        $icon = wl('admin_icon', '🤖');
        $name = wl('admin_name', 'Jessie');

        if ($logo) {
            return '<img src="' . htmlspecialchars($logo, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" style="max-height:32px">';
        }
        return '<span class="sidebar-logo-icon">' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . '</span><span>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</span>';
    }
}

if (!function_exists('wl_accent_css')) {
    function wl_accent_css(): string
    {
        $accent = wl('admin_accent', '#6366f1');
        if ($accent === '#6366f1') return ''; // default, no override needed
        // Generate accent overrides
        return "<style>:root,[data-theme=dark],[data-theme=light]{--color-accent:{$accent};--color-accent-hover:{$accent};--accent:{$accent}}</style>";
    }
}
