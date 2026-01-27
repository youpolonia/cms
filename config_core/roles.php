<?php

return [
    'roles' => [
        'super_admin' => [
            'permissions' => ['*'],
            'description' => 'Full system access'
        ],
        'content_admin' => [
            'permissions' => [
                'manage_content',
                'manage_media',
                'view_reports'
            ],
            'description' => 'Content management access'
        ],
        'moderator' => [
            'permissions' => [
                'moderate_content',
                'view_reports'
            ],
            'description' => 'Content moderation access'
        ]
    ],
    
    'permission_map' => [
        'access_admin' => 'Access admin dashboard',
        'manage_content' => 'Create/edit/delete content',
        'manage_media' => 'Upload/manage media',
        'moderate_content' => 'Approve/reject content',
        'view_reports' => 'View analytics reports',
        'manage_users' => 'Manage user accounts',
        'manage_settings' => 'Change system settings'
    ]
];
