# RBAC Enhancement Plan

## Current Issues
1. Missing PermissionManager implementation
2. Inconsistent ID types (string vs integer)
3. No role hierarchy support
4. Static permission definitions only

## Proposed Solution

### 1. PermissionManager Class
```php
class PermissionManager {
    // Database operations
    public static function assignRoleToUser(int $userId, int $roleId): bool
    public static function assignPermissionToRole(int $roleId, int $permissionId): bool
    
    // Hierarchy support
    public static function getChildRoles(int $roleId): array
    public static function getInheritedPermissions(int $roleId): array
    
    // Verification
    public static function userHasPermission(int $userId, string $permission): bool
}
```

### 2. Database Schema Updates
```sql
ALTER TABLE roles ADD parent_id INT NULL;
CREATE TABLE role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role_id, permission_id)
);
```

### 3. Access Middleware Update
- Replace static checks with PermissionManager calls
- Add hierarchy resolution
- Support both string and integer IDs

### 4. Migration Path
1. Implement PermissionManager
2. Update database schema
3. Migrate existing permissions
4. Update access.php middleware
5. Update admin interfaces

## Implementation Steps
1. Create `admin/permissions/PermissionManager.php`
2. Write database migration
3. Update `access.php`
4. Update `verify.php` for consistency
5. Test all permission scenarios