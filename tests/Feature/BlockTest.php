<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class BlockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles and permissions
        $adminRole = Role::create(['name' => 'admin']);
        $managerRole = Role::create(['name' => 'content-manager']);
        $editPermission = Permission::create(['name' => 'edit-locked-content']);
        $managerRole->givePermissionTo($editPermission);
    }

    /** @test */
    public function regular_users_cannot_modify_locked_blocks()
    {
        $user = User::factory()->create();
        $block = Block::factory()->locked()->create();

        $this->actingAs($user)
            ->putJson("/api/blocks/{$block->id}", ['content' => ['type' => 'paragraph', 'content' => 'new content']])
            ->assertForbidden();
    }

    /** @test */
    public function admins_can_modify_locked_blocks()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        $block = Block::factory()->locked()->create();

        $this->actingAs($admin)
            ->putJson("/api/blocks/{$block->id}", ['content' => ['type' => 'paragraph', 'content' => 'new content']])
            ->assertOk();
    }

    /** @test */
    public function content_managers_with_permission_can_modify_locked_blocks()
    {
        $manager = User::factory()->create();
        $manager->assignRole('content-manager');
        $manager->givePermissionTo('edit-locked-content');
        
        $block = Block::factory()->locked()->create();

        $this->actingAs($manager)
            ->putJson("/api/blocks/{$block->id}", ['content' => ['type' => 'paragraph', 'content' => 'new content']])
            ->assertOk();
    }

    /** @test */
    public function unlocked_blocks_can_be_modified_by_owners()
    {
        $user = User::factory()->create();
        $block = Block::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->putJson("/api/blocks/{$block->id}", ['content' => ['type' => 'paragraph', 'content' => 'new content']])
            ->assertOk();
    }
}