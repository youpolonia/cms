# RBAC System Documentation

## System Overview
The Role-Based Access Control (RBAC) system provides a flexible way to manage permissions through roles. Key features:
- Users are assigned roles
- Roles are granted permissions
- Middleware enforces permission checks
- Cascading deletes maintain referential integrity

## Core Components

### Database Schema
- `roles`: Stores role definitions (name, description)
- `permissions`: Stores permission definitions (name, description)
- `role_permissions`: Many-to-many relationship between roles and permissions
- `user_roles`: Many-to-many relationship between users and roles

### Models
#### Role (`app/Models/Role.php`)
- `permissions()`: Returns all permissions assigned to this role (SQL query)
- `users()`: Returns all users assigned to this role (SQL query)
- `givePermissionTo($permissionName)`: Grants permission to role
- `hasPermissionTo($permissionName)`: Checks if role has permission

#### Permission (`app/Models/Permission.php`)
- `roles()`: Returns all roles with this permission (SQL query)
- `assignToRole($roleName)`: Assigns permission to role
- `removeFromRole($roleName)`: Removes permission from role

#### User (`app/Models/User.php`)
- `roles()`: Returns all roles assigned to user (SQL query)
- `hasPermissionTo($permission)`: Checks permission through roles
- `assignRole($roleName)`: Assigns role to user
- `hasRole($roleName)`: Checks role assignment
- `getAllPermissions()`: Returns all permissions through roles (SQL query)

### Middleware
#### CheckPermission (`app/Middleware/CheckPermission.php`)
- Enforces permission checks on routes
- Redirects unauthenticated users
- Returns 403 for unauthorized access

## Permission Structure
Permissions are granted to roles, which are then assigned to users. All permissions are defined in `includes/Constants/Permissions.php`. The hierarchy is:

1. System defines permissions (see Permissions class)
2. Permissions are assigned to roles
3. Roles are assigned to users
4. Middleware checks user permissions

### Detailed Permission Hierarchy

#### Core RBAC Permissions
- `manage_users`: Required for all user management
- `manage_roles`: Required for role definitions
- `manage_permissions`: Required for permission assignments
- `assign_roles`: Required for user-role assignments
- `access_admin`: Required for administrative interface access

#### Content Permissions
- `create_content`: Base permission for content creation
- `edit_content`: Requires `create_content`
- `delete_content`: Standalone destructive action
- `publish_content`: Requires `edit_content`
- `view_unpublished`: View unpublished/draft content

#### Scheduling Permissions
- `create_schedule`: Base permission for scheduling (Permissions::CREATE_SCHEDULE)
- `edit_schedule`: Requires `create_schedule` (Permissions::EDIT_SCHEDULE)
- `delete_schedule`: Standalone destructive action (Permissions::DELETE_SCHEDULE)
- `view_schedule`: Read-only access (Permissions::VIEW_SCHEDULE)
- `schedule_content`: Schedule content for future publishing
- `view_scheduled_content`: View scheduled content
- `view_content_versions`: View content version history

#### Permission Relationships
1. Core RBAC permissions are independent of content/scheduling
2. Content permissions may be required for scheduling (e.g. scheduling content)
3. Scheduling permissions require corresponding content permissions:
   - `create_schedule` requires `create_content`
   - `edit_schedule` requires `edit_content`
   - `schedule_content` requires `create_schedule`
   - `view_scheduled_content` requires `view_schedule`
4. `access_admin` required for all administrative actions
5. `view_unpublished` required to see scheduled content before publish date

### Permission Constants Usage
Always use the constants from `Permissions` class rather than string literals:

```php
use App\Constants\Permissions;

if ($user->hasPermissionTo(Permissions::EDIT_CONTENT)) {
    // Allow editing
}
```

## Usage Examples

### Assigning Permissions
```php
$adminRole = Role::where('name', 'admin')->first();
$adminRole->givePermissionTo('manage_users');
```

### Checking Permissions
```php
if ($user->hasPermissionTo('edit_content')) {
    // Allow editing
}
```

### Middleware Usage
```php
Route::get('/admin/users', function () {
    // User management
})->middleware('permission:manage_users');
```

## Integration Guidelines
1. Add middleware to protected routes:
```php
Route::group(['middleware' => ['auth', 'permission:required_permission']], function () {
    // Protected routes
});
```

2. Implementation Notes:
- All model relationships use direct SQL queries rather than ORM
- Methods return arrays of objects rather than query builders
- Database tables follow standard naming conventions:
  * `roles`: id, name, description
  * `permissions`: id, name, description
  * `role_permission`: role_id, permission_id
  * `user_role`: user_id, role_id

## Maintenance Procedures
1. Adding new permissions:
- Create migration for new permission
- Add to database
- Assign to appropriate roles
- Update Permissions.php constants
- Update documentation

2. Error Handling:
- Database operations use transactions for atomicity
- Failed permission checks return 403 Forbidden
- Missing role/permission lookups throw RoleNotFoundException/PermissionNotFoundException
- All database operations include error handling with rollback on failure

2. Audit permissions:
```php
$user->getAllPermissions(); // Returns all permissions through roles
```

3. Cleanup:
- Unused permissions can be safely deleted
- Role deletions cascade to user assignments
- Permission deletions cascade to role assignments