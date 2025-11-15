<?php

class PermissionsManifest {
    public const ROLES = [
        'super_admin' => 'Super Administrator',
        'admin' => 'Administrator',
        'editor' => 'Content Editor',
        'author' => 'Content Author',
        'contributor' => 'Contributor',
        'subscriber' => 'Subscriber'
    ];

    public const PERMISSIONS = [
        // System permissions
        'manage_system' => 'Manage system settings',
        'manage_users' => 'Manage users and roles',
        
        // Content permissions
        'create_content' => 'Create new content',
        'edit_content' => 'Edit existing content',
        'delete_content' => 'Delete content',
        'publish_content' => 'Publish content',
        
        // Media permissions
        'upload_media' => 'Upload media files',
        'edit_media' => 'Edit media metadata',
        'delete_media' => 'Delete media files'
    ];

    public const ROLE_PERMISSIONS = [
        'super_admin' => ['*'],
        'admin' => [
            'manage_users',
            'create_content',
            'edit_content',
            'delete_content',
            'publish_content',
            'upload_media',
            'edit_media',
            'delete_media'
        ],
        'editor' => [
            'create_content',
            'edit_content',
            'publish_content',
            'upload_media',
            'edit_media'
        ],
        'author' => [
            'create_content',
            'edit_content',
            'upload_media'
        ],
        'contributor' => [
            'create_content',
            'edit_content'
        ]
    ];

    public static function getRolePermissions(string $role): array {
        return self::ROLE_PERMISSIONS[$role] ?? [];
    }

    public static function getAllPermissions(): array {
        return self::PERMISSIONS;
    }
}
