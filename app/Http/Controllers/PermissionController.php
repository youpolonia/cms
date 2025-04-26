<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService
    ) {}

    public function assignRole(User $user, Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        $role = Role::find($request->role_id);
        $this->permissionService->assignRole($user, $role);

        return response()->json(['success' => true]);
    }

    public function revokeRole(User $user, Role $role)
    {
        $this->permissionService->revokeRole($user, $role);
        return response()->json(['success' => true]);
    }

    public function syncRoles(User $user, Request $request)
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id'
        ]);

        $this->permissionService->syncRoles($user, $request->roles);
        return response()->json(['success' => true]);
    }

    public function checkPermission(User $user, string $permission)
    {
        $hasPermission = $this->permissionService->userHasPermission($user, $permission);
        return response()->json(['has_permission' => $hasPermission]);
    }

    public function getUserPermissions(User $user)
    {
        $permissions = $this->permissionService->getUserPermissions($user);
        return response()->json($permissions);
    }

    public function assignPermissionToRole(Role $role, Request $request)
    {
        $request->validate([
            'permission' => 'required|string'
        ]);

        $this->permissionService->assignPermissionToRole($role, $request->permission);
        return response()->json(['success' => true]);
    }

    public function revokePermissionFromRole(Role $role, Request $request)
    {
        $request->validate([
            'permission' => 'required|string'
        ]);

        $this->permissionService->revokePermissionFromRole($role, $request->permission);
        return response()->json(['success' => true]);
    }
}