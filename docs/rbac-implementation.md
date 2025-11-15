# RBAC Implementation

## Database Schema

```mermaid
erDiagram
    PERMISSIONS ||--o{ ROLE_PERMISSIONS : has
    ROLES ||--o{ ROLE_PERMISSIONS : has
    ROLES ||--o{ USER_SITE_ROLES : has
    USERS ||--o{ USER_SITE_ROLES : has
    SITES ||--o{ USER_SITE_ROLES : has
    
    PERMISSIONS {
        string id PK
        string name
        string description
        string category
        string parent_id FK
    }
    
    ROLE_PERMISSIONS {
        string role_id FK
        string permission_id FK
    }
    
    ROLES {
        int id PK
        string name
        string description
    }
    
    USER_SITE_ROLES {
        int id PK
        int user_id FK
        int role_id FK
        int site_id FK
    }
```

## Core Components

1. **PermissionManager**
   - Handles permission definitions and hierarchy
   - Manages permission inheritance
   - Provides permission validation

2. **RoleManager** 
   - Manages role-permission assignments
   - Handles role creation/modification
   - Provides role validation

3. **AccessChecker**
   - Performs permission checks
   - Handles permission caching
   - Provides audit logging

4. **PermissionRegistry**
   - Central registry of all permissions
   - Handles permission discovery
   - Provides permission metadata

## Integration Points

1. **Authentication System**
   - Role assignments during user creation
   - Permission checks during login

2. **Routing Middleware**
   - Route-level permission checks
   - Automatic permission validation

3. **Admin Panel UI**
   - Role management interface
   - Permission assignment UI
   - Access control visualization

4. **Plugin System**
   - Plugin permission registration
   - Plugin-specific access controls
   - Permission-aware plugin loading

## Usage Examples

```php
// Check if user has permission
if ($accessChecker->hasPermission($user, 'content.edit')) {
    // Allow content editing
}

// Get all permissions for role
$permissions = $roleManager->getPermissionsForRole($roleId);

// Register new permission
$permissionManager->registerPermission([
    'id' => 'content.publish',
    'name' => 'Publish Content',
    'category' => 'content'
]);