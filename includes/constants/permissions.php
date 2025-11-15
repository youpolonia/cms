<?php

namespace App\Constants;

/**
 * System-wide permission constants
 * 
 * All permissions should be defined here with clear documentation
 * about their purpose and relationships to other permissions.
 */
class Permissions
{
    // Core RBAC Permissions
    const MANAGE_USERS = 'manage_users';
    const MANAGE_ROLES = 'manage_roles';
    const MANAGE_PERMISSIONS = 'manage_permissions';
    const ASSIGN_ROLES = 'assign_roles';
    const ACCESS_ADMIN = 'access_admin';

    // Content Permissions
    const CREATE_CONTENT = 'create_content';
    const EDIT_CONTENT = 'edit_content';
    const DELETE_CONTENT = 'delete_content';
    const PUBLISH_CONTENT = 'publish_content';
    const VIEW_UNPUBLISHED = 'view_unpublished';

    // Scheduling Permissions
    const CREATE_SCHEDULE = 'create_schedule';
    const EDIT_SCHEDULE = 'edit_schedule';
    const DELETE_SCHEDULE = 'delete_schedule';
    const VIEW_SCHEDULE = 'view_schedule';

    /**
     * Get all permissions with descriptions
     * 
     * @return array Permission name => description
     */
    public static function all(): array
    {
        return [
            // Core RBAC
            self::MANAGE_USERS => 'Manage user accounts',
            self::MANAGE_ROLES => 'Manage role definitions',
            self::MANAGE_PERMISSIONS => 'Manage permission assignments',
            self::ASSIGN_ROLES => 'Assign roles to users',
            self::ACCESS_ADMIN => 'Access administrative interface',

            // Content
            self::CREATE_CONTENT => 'Create new content',
            self::EDIT_CONTENT => 'Edit existing content',
            self::DELETE_CONTENT => 'Delete content',
            self::PUBLISH_CONTENT => 'Publish content',
            self::VIEW_UNPUBLISHED => 'View unpublished content',

            // Scheduling
            self::CREATE_SCHEDULE => 'Create new schedules',
            self::EDIT_SCHEDULE => 'Modify existing schedules',
            self::DELETE_SCHEDULE => 'Delete schedules',
            self::VIEW_SCHEDULE => 'View scheduled content'
        ];
    }
}
