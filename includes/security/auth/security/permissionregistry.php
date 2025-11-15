<?php

class PermissionRegistry {
    private $permissionManager;
    private $registeredPermissions = [];

    public function __construct(PermissionManager $permissionManager) {
        $this->permissionManager = $permissionManager;
    }

    public function register(array $permission): void {
        if (!isset($permission['id'])) {
            throw new InvalidArgumentException("Permission ID is required");
        }

        $this->registeredPermissions[$permission['id']] = $permission;
        $this->permissionManager->registerPermission($permission);
    }

    public function registerBatch(array $permissions): void {
        foreach ($permissions as $permission) {
            $this->register($permission);
        }
    }

    public function getPermissionMetadata(string $permissionId): ?array {
        if (!isset($this->registeredPermissions[$permissionId])) {
            return null;
        }

        return [
            'id' => $this->registeredPermissions[$permissionId]['id'],
            'name' => $this->registeredPermissions[$permissionId]['name'] ?? '',
            'description' => $this->registeredPermissions[$permissionId]['description'] ?? '',
            'category' => $this->registeredPermissions[$permissionId]['category'] ?? 'general',
            'parent' => $this->registeredPermissions[$permissionId]['parent_id'] ?? null
        ];
    }

    public function getAllPermissions(): array {
        return array_values($this->registeredPermissions);
    }

    public function getPermissionsByCategory(string $category): array {
        return array_filter($this->registeredPermissions, 
            fn($perm) => ($perm['category'] ?? 'general') === $category);
    }
}
