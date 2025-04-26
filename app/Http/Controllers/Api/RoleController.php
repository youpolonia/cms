<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Role::with('permissions')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'description' => 'nullable|string'
        ]);

        $role = Role::create($validated);

        return response()->json([
            'data' => $role,
            'message' => 'Role created successfully'
        ], 201);
    }

    public function show(Role $role)
    {
        return response()->json([
            'data' => $role->load('permissions')
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'description' => 'nullable|string'
        ]);

        $role->update($validated);

        return response()->json([
            'data' => $role,
            'message' => 'Role updated successfully'
        ]);
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully'
        ]);
    }

    public function syncPermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role->permissions()->sync($request->permissions);

        return response()->json([
            'data' => $role->load('permissions'),
            'message' => 'Permissions synced successfully'
        ]);
    }

    public function assign(Request $request, Role $role)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = \App\Models\User::find($request->user_id);
        $user->assignRole($role);

        return response()->json([
            'message' => 'Role assigned successfully'
        ]);
    }

    public function revoke(Request $request, Role $role)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = \App\Models\User::find($request->user_id);
        $user->removeRole($role);

        return response()->json([
            'message' => 'Role revoked successfully'
        ]);
    }
}