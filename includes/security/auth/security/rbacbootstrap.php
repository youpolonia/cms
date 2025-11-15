<?php

class RbacBootstrap {
    public static function init($dbConnection): array {
        $permissionManager = new PermissionManager();
        $roleManager = new RoleManager($dbConnection, $permissionManager);
        $accessChecker = new AccessChecker($dbConnection, $roleManager, $permissionManager);
        $permissionRegistry = new PermissionRegistry($permissionManager);

        // Register core permissions
        $permissionRegistry->registerBatch([
            [
                'id' => 'content.create',
                'name' => 'Create Content',
                'category' => 'content'
            ],
            [
                'id' => 'content.edit',
                'name' => 'Edit Content',
                'category' => 'content'
            ],
            [
                'id' => 'content.delete',
                'name' => 'Delete Content',
                'category' => 'content'
            ],
            [
                'id' => 'user.manage',
                'name' => 'Manage Users',
                'category' => 'users'
            ]
        ]);

        return [
            'permissionManager' => $permissionManager,
            'roleManager' => $roleManager,
            'accessChecker' => $accessChecker,
            'permissionRegistry' => $permissionRegistry
        ];
    }
}
