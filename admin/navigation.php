<?php
/**
 * Admin Navigation Module
 * Renders the admin navigation menu with security integration
 */

declare(strict_types=1);

if (!defined('ADMIN_NAVIGATION_INIT')) {
    define('ADMIN_NAVIGATION_INIT', true);
    
    require_once __DIR__ . '/security.php';
}

/**
 * Render admin navigation menu
 * @return string HTML for admin navigation
 */
function renderAdminNavigation(): string {
    if (!verifyAdminAccess()) {
        return '';
    }

    $navItems = [
        'dashboard' => [
            'title' => 'Dashboard',
            'url' => '/admin/index.php',
            'icon' => 'dashboard'
        ],
        'content' => [
            'title' => 'Content',
            'url' => '/admin/content/create.php',
            'icon' => 'edit'
        ],
        'settings' => [
            'title' => 'Settings',
            'url' => '/admin/settings.php',
            'icon' => 'settings'
        ],
        'analytics' => [
            'title' => 'Analytics',
            'url' => '/admin/analytics-dashboard.php',
            'icon' => 'analytics'
        ],
        'security' => [
            'title' => 'Security',
            'url' => '/admin/security.php',
            'icon' => 'security'
        ]
    ];

    $csrfToken = generateAdminCsrfToken();
    $output = '<nav class="admin-navigation">
<ul>';
    foreach ($navItems as $key => $item) {
        $output .= sprintf(
            '<li><a href="%s?csrf_token=%s" data-nav="%s"><i class="icon-%s"></i> %s</a></li>',
            htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($key, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8')
        );
    }
    
    $output .= '
</ul></nav>';

    return $output;
}
