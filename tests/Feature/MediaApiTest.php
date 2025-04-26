<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\MediaCollection;
use App\Models\MediaVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MediaApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected Media $media;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->media = Media::factory()->create(['user_id' => $this->user->id]);
    }

    #[Test]
    public function user_can_update_media_metadata()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/media/{$this->media->id}/update-metadata", [
                'filename' => 'Updated Filename',
                'description' => 'Updated description'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'filename' => 'Updated Filename',
                'description' => 'Updated description'
            ]);

        $this->assertDatabaseHas('media', [
            'id' => $this->media->id,
            'filename' => 'Updated Filename',
            'description' => 'Updated description'
        ]);
    }

    #[Test]
    public function user_cannot_update_others_media_metadata()
    {
        $response = $this->actingAs($this->otherUser)
            ->postJson("/api/media/{$this->media->id}/update-metadata", [
                'filename' => 'Updated Filename',
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function update_metadata_requires_valid_data()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/media/{$this->media->id}/update-metadata", [
                'filename' => str_repeat('a', 256), // too long
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['filename']);
    }

    #[Test]
    public function user_can_update_media_collections()
    {
        $collections = MediaCollection::factory()
            ->count(2)
            ->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/media/{$this->media->id}/update-collections", [
                'collections' => $collections->pluck('id')->toArray(),
                'featured' => [$collections[0]->id]
            ]);

        $response->assertStatus(200);

        $this->assertEquals(2, $this->media->collections()->count());
        $this->assertTrue($this->media->collections()->where('id', $collections[0]->id)->first()->pivot->is_featured);
    }

    #[Test]
    public function user_cannot_update_media_with_others_collections()
    {
        $collection = MediaCollection::factory()
            ->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/media/{$this->media->id}/update-collections", [
                'collections' => [$collection->id]
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['collections.0']);
    }

    #[Test]
    public function update_collections_requires_valid_data()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/media/{$this->media->id}/update-collections", [
                'collections' => 'not-an-array'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['collections']);
    }

    #[Test]
    public function user_can_list_media_versions()
    {
        $versions = MediaVersion::factory()
            ->count(3)
            ->create(['media_id' => $this->media->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/media/{$this->media->id}/versions");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function user_can_compare_versions()
    {
        $version1 = MediaVersion::factory()
            ->create(['media_id' => $this->media->id, 'version_number' => 1]);
        
        $version2 = MediaVersion::factory()
            ->create(['media_id' => $this->media->id, 'version_number' => 2]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/media/{$this->media->id}/versions/compare?version1=1&version2=2");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'version1',
                'version2',
                'differences'
            ]);
    }

    #[Test]
    public function user_can_get_version_diff()
    {
        $version1 = MediaVersion::factory()
            ->create(['media_id' => $this->media->id, 'version_number' => 1]);
        
        $version2 = MediaVersion::factory()
            ->create(['media_id' => $this->media->id, 'version_number' => 2]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/media/{$this->media->id}/versions/diff?version1=1&version2=2");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'version1',
                'version2',
                'diffs'
            ]);
    }

    #[Test]
    public function user_can_restore_version()
    {
        $version = MediaVersion::factory()
            ->create(['media_id' => $this->media->id, 'version_number' => 1]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/media/{$this->media->id}/versions/restore", [
                'version_number' => 1
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Restored to version 1 successfully'
            ]);
    }

    #[Test]
    public function restore_requires_valid_version()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/media/{$this->media->id}/versions/restore", [
                'version_number' => 999
            ]);

        $response->assertStatus(404);
    }
}
