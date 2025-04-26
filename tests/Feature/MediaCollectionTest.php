<?php

namespace Tests\Feature;

use App\Models\MediaCollection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MediaCollectionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
    }

    #[Test]
    public function user_can_create_collection()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/media-collections', [
                'name' => 'Test Collection',
                'description' => 'Test description',
                'is_private' => true,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'name' => 'Test Collection',
                'slug' => 'test-collection',
                'description' => 'Test description',
                'is_private' => true,
                'user_id' => $this->user->id,
            ]);
    }

    #[Test]
    public function slug_is_generated_from_name()
    {
        $collection = MediaCollection::factory()
            ->create(['name' => 'My Test Collection', 'user_id' => $this->user->id]);

        $this->assertEquals('my-test-collection', $collection->slug);
    }

    #[Test]
    public function duplicate_names_generate_unique_slugs()
    {
        $collection1 = MediaCollection::factory()
            ->create(['name' => 'Test Collection', 'user_id' => $this->user->id]);

        $collection2 = MediaCollection::factory()
            ->create(['name' => 'Test Collection', 'user_id' => $this->user->id]);

        $this->assertEquals('test-collection', $collection1->slug);
        $this->assertEquals('test-collection-1', $collection2->slug);
    }

    #[Test]
    public function user_can_view_own_private_collection()
    {
        $collection = MediaCollection::factory()
            ->create(['user_id' => $this->user->id, 'is_private' => true]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/media-collections/{$collection->id}");

        $response->assertStatus(200)
            ->assertJson(['id' => $collection->id]);
    }

    #[Test]
    public function user_cannot_view_others_private_collections()
    {
        $collection = MediaCollection::factory()
            ->create(['user_id' => $this->otherUser->id, 'is_private' => true]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/media-collections/{$collection->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function user_can_view_public_collections()
    {
        $collection = MediaCollection::factory()
            ->create(['user_id' => $this->otherUser->id, 'is_private' => false]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/media-collections/{$collection->id}");

        $response->assertStatus(200)
            ->assertJson(['id' => $collection->id]);
    }

    #[Test]
    public function user_can_update_own_collection()
    {
        $collection = MediaCollection::factory()
            ->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/media-collections/{$collection->id}", [
                'name' => 'Updated Name',
                'is_private' => true,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'name' => 'Updated Name',
                'is_private' => true,
            ]);
    }

    #[Test]
    public function user_cannot_update_others_collections()
    {
        $collection = MediaCollection::factory()
            ->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/media-collections/{$collection->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function user_can_delete_own_collection()
    {
        $collection = MediaCollection::factory()
            ->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/media-collections/{$collection->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('media_collections', ['id' => $collection->id]);
    }

    #[Test]
    public function user_cannot_delete_others_collections()
    {
        $collection = MediaCollection::factory()
            ->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/media-collections/{$collection->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('media_collections', ['id' => $collection->id]);
    }

    #[Test]
    public function user_can_manage_items_in_own_collection()
    {
        $collection = MediaCollection::factory()
            ->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/media-collections/{$collection->id}/items", [
                'media_ids' => [1, 2, 3],
            ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function user_cannot_manage_items_in_others_collections()
    {
        $collection = MediaCollection::factory()
            ->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/media-collections/{$collection->id}/items", [
                'media_ids' => [1, 2, 3],
            ]);

        $response->assertStatus(403);
    }
}
