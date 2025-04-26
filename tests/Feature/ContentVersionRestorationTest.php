<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\ContentRollback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentVersionRestorationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $content;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->content = Content::factory()->create(['user_id' => $this->user->id]);
    }

    /** @test */
    public function authenticated_user_can_restore_previous_version()
    {
        $version1 = ContentVersion::factory()->create([
            'content_id' => $this->content->id,
            'version_number' => 1,
            'content' => 'Original content'
        ]);

        $version2 = ContentVersion::factory()->create([
            'content_id' => $this->content->id,
            'version_number' => 2,
            'content' => 'Updated content'
        ]);

        $this->actingAs($this->user)
            ->post(route('content.versions.restore', [
                'content' => $this->content,
                'version' => $version1
            ]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('content_versions', [
            'content_id' => $this->content->id,
            'version_number' => 3,
            'restored_from_version_id' => $version1->id
        ]);

        $this->assertEquals('Original content', $this->content->fresh()->current_content);
    }

    /** @test */
    public function test_version_comparison_returns_differences()
    {
        $version1 = ContentVersion::factory()->create([
            'content_id' => $this->content->id,
            'content' => 'Original content'
        ]);

        $version2 = ContentVersion::factory()->create([
            'content_id' => $this->content->id,
            'content' => 'Updated content'
        ]);

        $this->actingAs($this->user)
            ->get(route('content.versions.compare', [
                'content' => $this->content,
                'version1' => $version1,
                'version2' => $version2
            ]))
            ->assertOk()
            ->assertJsonStructure([
                'differences'
            ]);
    }

    /** @test */
    public function prepare_rollback_creates_rollback_record()
    {
        $version = ContentVersion::factory()->create([
            'content_id' => $this->content->id,
            'content' => 'Version to rollback to'
        ]);

        $this->actingAs($this->user)
            ->post(route('content.versions.prepare-rollback', [
                'content' => $this->content,
                'version' => $version
            ]), [
                'reason' => 'Testing rollback'
            ])
            ->assertOk()
            ->assertJsonStructure([
                'rollback_id'
            ]);

        $this->assertDatabaseHas('content_rollbacks', [
            'content_id' => $this->content->id,
            'version_id' => $version->id,
            'user_id' => $this->user->id,
            'reason' => 'Testing rollback',
            'confirmed' => false
        ]);
    }

    /** @test */
    public function confirm_rollback_updates_content()
    {
        $version = ContentVersion::factory()->create([
            'content_id' => $this->content->id,
            'content' => 'Version to rollback to'
        ]);

        $rollback = ContentRollback::create([
            'content_id' => $this->content->id,
            'version_id' => $version->id,
            'user_id' => $this->user->id,
            'reason' => 'Testing rollback'
        ]);

        $this->actingAs($this->user)
            ->post(route('content.versions.confirm-rollback', [
                'content' => $this->content,
                'version' => $version,
                'rollback' => $rollback
            ]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('content_rollbacks', [
            'id' => $rollback->id,
            'confirmed' => true
        ]);

        $this->assertEquals('Version to rollback to', $this->content->fresh()->current_content);
    }

    /** @test */
    public function guest_cannot_restore_versions()
    {
        $version = ContentVersion::factory()->create([
            'content_id' => $this->content->id
        ]);

        $this->post(route('content.versions.restore', [
            'content' => $this->content,
            'version' => $version
        ]))->assertRedirect('/login');
    }

    /** @test */
    public function unauthorized_user_cannot_restore_versions()
    {
        $otherUser = User::factory()->create();
        $version = ContentVersion::factory()->create([
            'content_id' => $this->content->id
        ]);

        $this->actingAs($otherUser)
            ->post(route('content.versions.restore', [
                'content' => $this->content,
                'version' => $version
            ]))
            ->assertForbidden();
    }
}