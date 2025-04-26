<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\ContentVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentVersionComparisonControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_compare_two_versions()
    {
        $content = Content::factory()->create();
        $version1 = ContentVersion::factory()->for($content)->create();
        $version2 = ContentVersion::factory()->for($content)->create();

        $response = $this->actingAs($this->createUser())
            ->getJson("/api/content-versions/compare/{$version1->id}/{$version2->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'differences',
                    'summary'
                ]
            ]);
    }

    public function test_cannot_compare_versions_from_different_content()
    {
        $version1 = ContentVersion::factory()->create();
        $version2 = ContentVersion::factory()->create();

        $response = $this->actingAs($this->createUser())
            ->getJson("/api/content-versions/compare/{$version1->id}/{$version2->id}");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Versions must belong to the same content'
            ]);
    }

    public function test_compare_version_to_latest()
    {
        $content = Content::factory()->create();
        $version1 = ContentVersion::factory()->for($content)->create();
        $version2 = ContentVersion::factory()->for($content)->create();

        $response = $this->actingAs($this->createUser())
            ->getJson("/api/content-versions/{$version1->id}/compare-to-latest");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'compared_to' => $version2->id
                ]
            ]);
    }

    public function test_get_comparison_history()
    {
        $content = Content::factory()->create();
        ContentVersion::factory()->count(3)->for($content)->create();

        $response = $this->actingAs($this->createUser())
            ->getJson("/api/content-versions/{$content->id}/comparison-history");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'diffs'
                        ]
                    ]
                ]
            ]);
    }

    private function createUser()
    {
        return \App\Models\User::factory()->create();
    }
}