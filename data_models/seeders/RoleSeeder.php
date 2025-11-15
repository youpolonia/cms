<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['name' => 'Super Admin', 'guard_name' => 'web'],
            ['name' => 'Content Moderator', 'guard_name' => 'web'],
            ['name' => 'Editor', 'guard_name' => 'web'],
            ['name' => 'Contributor', 'guard_name' => 'web'],
            ['name' => 'Viewer', 'guard_name' => 'web'],
        ];

        foreach ($roles as $roleData) {
            $role = Role::create($roleData);

            // Assign permissions based on role name with validation
            switch ($role->name) {
                case 'Super Admin':
                    $this->assignPermissions($role, Permission::all()->pluck('name')->toArray());
                    break;
                case 'Content Moderator':
                    $this->assignPermissions($role, [
                        'content.create',
                        'content.edit',
                        'content.moderate',
                        'analytics.view'
                    ]);
                    break;
                case 'Editor':
                    $this->assignPermissions($role, [
                        'content.create',
                        'content.edit',
                        'analytics.view'
                    ]);
                    break;
                case 'Contributor':
                    $this->assignPermissions($role, ['content.create']);
                    break;
            }
        }
    }

    protected function assignPermissions(Role $role, array $permissionNames)
    {
        $validPermissions = [];
        $missingPermissions = [];

        foreach ($permissionNames as $permissionName) {
            if (Permission::where('name', $permissionName)->exists()) {
                $validPermissions[] = $permissionName;
            } else {
                $missingPermissions[] = $permissionName;
            }
        }

        if (!empty($validPermissions)) {
            $role->givePermissionTo($validPermissions);
        }

        if (!empty($missingPermissions)) {
            Log::warning("Missing permissions when assigning to role {$role->name}: " . implode(', ', $missingPermissions));
        }
    }
}
