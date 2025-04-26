<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\ContentVersionDiff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentVersionComparisonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Handle existing table before migrations
        if (\Schema::hasTable('content_version_diffs')) {
            \Schema::drop('content_version_diffs');
        }
        
        $this->user = User::factory()->create();
        $this->content = Content::factory()->create();
    }
    
    protected function tearDown(): void
    {
        // Clean up after tests
        if (\Schema::hasTable('content_version_diffs')) {
            \Schema::drop('content_version_diffs');
        }
        
        parent::tearDown();
    }

    public function test_version_comparison_index()
    {
        $versions = ContentVersion::factory()
            ->count(3)
            ->for($this->content)
            ->create();

        $response = $this->actingAs($this->user)
            ->get(route('content.version-comparison.index', $this->content));

        $response->assertOk();
        $response->assertViewHas('versions');
        $this->assertCount(3, $response->viewData('versions'));
    }

    public function test_version_comparison_creation()
    {
        $version1 = ContentVersion::factory()
            ->for($this->content)
            ->create(['content' => 'First version']);
        
        $version2 = ContentVersion::factory()
            ->for($this->content)
            ->create(['content' => 'Second version']);

        $response = $this->actingAs($this->user)
            ->post(route('content.version-comparison.compare', $this->content), [
                'from_version' => $version1->id,
                'to_version' => $version2->id
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('content_version_diffs', [
            'from_version_id' => $version1->id,
            'to_version_id' => $version2->id
        ]);
    }

    public function test_version_comparison_view()
    {
        $version1 = ContentVersion::factory()
            ->for($this->content)
            ->create(['content' => 'First version']);
        
        $version2 = ContentVersion::factory()
            ->for($this->content)
            ->create(['content' => 'Second version']);

        $diff = ContentVersionDiff::create([
            'content_id' => $this->content->id,
            'from_version_id' => $version1->id,
            'to_version_id' => $version2->id,
            'diff_data' => ['changes' => ['-First version', '+Second version']],
            'summary' => 'Test comparison'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('content.version-comparison.show', [
                'content' => $this->content,
                'diff' => $diff
            ]));

        $response->assertOk();
        $response->assertSee('Version Comparison Results');
    }

    public function test_version_comparison_deletion()
    {
        $version1 = ContentVersion::factory()
            ->for($this->content)
            ->create();
        
        $version2 = ContentVersion::factory()
            ->for($this->content)
            ->create();

        $diff = ContentVersionDiff::create([
            'content_id' => $this->content->id,
            'from_version_id' => $version1->id,
            'to_version_id' => $version2->id,
            'diff_data' => ['changes' => []],
            'summary' => 'Test comparison'
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('content.version-comparison.destroy', [
                'content' => $this->content,
                'diff' => $diff
            ]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('content_version_diffs', [
            'id' => $diff->id
        ]);
    }
}