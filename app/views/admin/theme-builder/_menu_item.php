<?php
/**
 * Theme Builder 3.0 - Admin Menu Item
 * Merge this into admin_menu.php navigation
 */
return [
    'title' => 'Theme Builder',
    'icon' => 'layout',
    'url' => '/admin/theme-builder',
    'permission' => 'manage_themes',
    'badge' => '3.0',
    'children' => [
        [
            'title' => 'Pages',
            'url' => '/admin/theme-builder',
            'permission' => 'manage_themes'
        ],
        [
            'title' => 'Create Page',
            'url' => '/admin/theme-builder/create',
            'permission' => 'manage_themes'
        ],
        [
            'title' => 'Templates',
            'url' => '/admin/theme-builder/templates',
            'permission' => 'manage_themes'
        ]
    ]
];
