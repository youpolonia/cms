# RBAC User Guide

## Introduction
This guide explains how to use the Role-Based Access Control (RBAC) system as an administrator or content manager.

## User Roles
### Available Roles
1. **Admin**: Full system access
2. **Editor**: Can create/edit content
3. **Author**: Can create content
4. **Viewer**: Read-only access

### Assigning Roles
1. Navigate to User Management
2. Select a user
3. Click "Edit Roles"
4. Check desired roles
5. Save changes

## Permission Management
### Viewing Permissions
1. Go to Roles & Permissions
2. Select a role to view its permissions
3. Permissions are shown with both their constant names (e.g. `Permissions::CREATE_SCHEDULE`) and descriptions

### Assigning Permissions
1. Navigate to Role Management
2. Select a role
3. Click "Edit Permissions"
4. Check/uncheck permissions (use constant names from Permissions class)
5. Save changes
6. System validates permission dependencies automatically

## Common Tasks
### Checking Your Permissions
1. Click your profile icon
2. Select "My Permissions"
3. View list of all permissions through your roles

### Requesting Additional Access
1. Contact your system administrator
2. Specify which permissions you need
3. Explain business justification

## Troubleshooting
### "Access Denied" Errors
1. Verify you're logged in
2. Check if your role has the required permission
3. Verify permission dependencies are met (e.g. `edit_schedule` requires `create_schedule`)
4. Check system logs for detailed error messages
5. Contact admin if access is needed

### Role Changes Not Taking Effect
1. Log out and back in
2. Clear browser cache
3. Wait up to 5 minutes for system propagation
4. Check for transaction errors in system logs

### Database Errors
1. Check for "Duplicate entry" errors when assigning permissions
2. Verify foreign key constraints are satisfied
3. Review transaction logs if operations fail mid-process

## Best Practices
1. Always use permission constants (`Permissions::CONST_NAME`) rather than strings
2. Follow principle of least privilege
3. Audit permissions quarterly
4. Remove unused roles/permissions
5. Document permission changes
6. Test permission changes in staging first
7. Verify error handling for missing roles/permissions