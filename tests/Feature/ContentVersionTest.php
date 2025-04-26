<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ContentVersionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Content $content;
    protected ContentVersion $version;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->givePermissionTo('edit content');
        
        $this->content = Content::factory()->create([
            'user_id' => $this->user->id,
            'content' => 'Current content',
            'seo_keywords' => json_encode(['test', 'keywords'])
        ]);
        $this->version = ContentVersion::factory()->create([
            'content_id' => $this->content->id,
            'content_data' => json_encode(['body' => 'Old content version']),
            'user_id' => $this->user->id,
            'version_number' => 1
        ]);
    }

    #[Test]
    public function user_can_restore_a_version()
    {
        $this->actingAs($this->user);
        
        // Bypass authorization for testing
        $this->withoutMiddleware();

        $response = $this->post(route('content.versions.restore', [
            'content' => $this->content,
            'version' => $this->version
        ]));

        $response->assertRedirect(route('content.show', $this->content))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('contents', [
            'id' => $this->content->id,
            'content' => 'Old content version',
            'restore_count' => 1
        ]);

        $this->assertDatabaseHas('content_versions', [
            'id' => $this->version->id,
            'restore_count' => 1
        ]);
    }

    #[Test]
    public function unauthorized_user_cannot_restore_a_version()
    {
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        $response = $this->post(route('content.versions.restore', [
            'content' => $this->content,
            'version' => $this->version
        ]));

        $response->assertForbidden();
    }
}
