<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Collection;

class PermissionService
{
    public function assignRole(User $user, Role $role): void
    {
        $user->roles()->syncWithoutDetaching([$role->id]);
    }

    public function revokeRole(User $user, Role $role): void
    {
        $user->roles()->detach($role->id);
    }

    public function syncRoles(User $user, array $roleIds): void
    {
        $user->roles()->sync($roleIds);
    }

    public function userHasPermission(User $user, string $permission): bool
    {
        return $user->roles()
            ->whereHas('permissions', function($query) use ($permission) {
                $query->where('name', $permission);
            })
            ->exists();
    }

    public function getUserPermissions(User $user): Collection
    {
        return $user->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id');
    }

    public function getRolePermissions(Role $role): Collection
    {
        return $role->permissions;
    }

    public function assignPermissionToRole(Role $role, string $permission): void
    {
        $role->permissions()->firstOrCreate(['name' => $permission]);
    }

    public function revokePermissionFromRole(Role $role, string $permission): void
    {
        $role->permissions()->where('name', $permission)->delete();
    }
}