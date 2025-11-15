<?php

class PermissionManager {
    private $permissions = [];
    private $permissionHierarchy = [];

    public function registerPermission(array $permission): void {
        if (!isset($permission['id'])) {
            throw new InvalidArgumentException("Permission ID is required");
        }

        $this->permissions[$permission['id']] = $permission;

        if (isset($permission['parent_id'])) {
            $this->permissionHierarchy[$permission['parent_id']][] = $permission['id'];
        }
    }

    public function getPermission(string $permissionId): ?array {
        return $this->permissions[$permissionId] ?? null;
    }

    public function getChildPermissions(string $parentId): array {
        return $this->permissionHierarchy[$parentId] ?? [];
    }

    public function validatePermission(string $permissionId): bool {
        return isset($this->permissions[$permissionId]);
    }

    public function getAllPermissions(): array {
        return $this->permissions;
    }
}
