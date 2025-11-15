<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            ['name' => 'content.create', 'guard_name' => 'web'],
            ['name' => 'content.edit', 'guard_name' => 'web'],
            ['name' => 'content.delete', 'guard_name' => 'web'],
            ['name' => 'content.moderate', 'guard_name' => 'web'],
            ['name' => 'ai.generate', 'guard_name' => 'web'],
            ['name' => 'analytics.view', 'guard_name' => 'web'],
            ['name' => 'analytics.export', 'guard_name' => 'web'],
            ['name' => 'user.manage', 'guard_name' => 'web'],
            ['name' => 'role.manage', 'guard_name' => 'web'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
