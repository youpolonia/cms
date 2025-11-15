<?php

// config/permissions.php

return [
    'roles' => [
        'admin' => [
            'label' => 'Administrator',
            'permissions' => ['manage_users', 'manage_content', 'view_system_status', 'manage_settings'],
        ],
        'editor' => [
            'label' => 'Editor',
            'permissions' => ['manage_content'],
        ],
        'user' => [
            'label' => 'User',
            // Or specific frontend permissions if applicable
            'permissions' => [],
        ],
    ],
    'permissions_map' => [
        'manage_users' => 'Manage Users (CRUD operations)',
        'manage_content' => 'Manage Content (CRUD operations)',
        'view_system_status' => 'View System Status Page',
        'manage_settings' => 'Manage System Settings',
        // Add more granular permissions as needed later
    ]
];
