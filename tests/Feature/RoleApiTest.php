<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RoleApiTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_can_list_roles()
    {
        Role::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/roles');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_role()
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/roles', [
                'name' => 'editor',
                'description' => 'Content Editor Role'
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'editor')
            ->assertJsonPath('message', 'Role created successfully');

        $this->assertDatabaseHas('roles', ['name' => 'editor']);
    }

    public function test_can_show_role()
    {
        $role = Role::factory()->create();

        $response = $this->actingAs($this->admin)
            ->getJson("/api/roles/{$role->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $role->id);
    }

    public function test_can_update_role()
    {
        $role = Role::factory()->create();

        $response = $this->actingAs($this->admin)
            ->putJson("/api/roles/{$role->id}", [
                'name' => 'updated-role',
                'description' => 'Updated description'
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'updated-role')
            ->assertJsonPath('message', 'Role updated successfully');

        $this->assertDatabaseHas('roles', ['name' => 'updated-role']);
    }

    public function test_can_delete_role()
    {
        $role = Role::factory()->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/roles/{$role->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Role deleted successfully');

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_can_sync_permissions()
    {
        $role = Role::factory()->create();
        $permissions = Permission::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->postJson("/api/roles/{$role->id}/permissions", [
                'permissions' => $permissions->pluck('id')->toArray()
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Permissions synced successfully')
            ->assertJsonCount(3, 'data.permissions');

        $this->assertCount(3, $role->fresh()->permissions);
    }
}