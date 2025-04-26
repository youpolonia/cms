<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\User;
use App\Models\VersionComparisonStat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VersionComparisonAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->content = Content::factory()->create();
        $this->versions = ContentVersion::factory()
            ->count(5)
            ->for($this->content)
            ->create();

        // Create some comparison stats
        VersionComparisonStat::factory()
            ->count(10)
            ->create([
                'content_id' => $this->content->id,
                'base_version_id' => $this->versions->random()->id,
                'target_version_id' => $this->versions->random()->id,
                'user_id' => $this->user->id
            ]);
    }

    /** @test */
    public function it_returns_dashboard_analytics()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/content/{$this->content->id}/version-comparison/analytics/dashboard");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'trends',
                'cache_stats',
                'frequent_comparisons',
                'user_activity',
                'version_timeline' => [
                    'versions',
                    'comparisons'
                ]
            ]);
    }

    /** @test */
    public function it_returns_enhanced_trends()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/content/{$this->content->id}/version-comparison/analytics/trends");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'stats' => [
                    'total',
                    'cache_hits',
                    'unique_users',
                    'version_pairs',
                    'daily_counts'
                ],
                'labels',
                'data'
            ]);
    }

    /** @test */
    public function it_returns_cache_performance()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/content/{$this->content->id}/version-comparison/analytics/cache-performance");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'hits',
                'misses',
                'hit_rate',
                'total'
            ]);
    }

    /** @test */
    public function it_returns_most_active_users()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/content/{$this->content->id}/version-comparison/analytics/active-users");

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'user_id',
                    'count'
                ]
            ]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $endpoints = [
            "/api/content/{$this->content->id}/version-comparison/analytics/dashboard",
            "/api/content/{$this->content->id}/version-comparison/analytics/trends",
            "/api/content/{$this->content->id}/version-comparison/analytics/cache-performance",
            "/api/content/{$this->content->id}/version-comparison/analytics/active-users"
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            $response->assertStatus(401);
        }
    }

    /** @test */
    public function it_validates_content_exists()
    {
        $invalidId = 9999;
        $response = $this->actingAs($this->user)
            ->getJson("/api/content/{$invalidId}/version-comparison/analytics/dashboard");

        $response->assertStatus(404);
    }

    /** @test */
    public function it_accepts_time_range_parameter_for_trends()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/content/{$this->content->id}/version-comparison/analytics/trends?time_range=7d");

        $response->assertStatus(200);
    }
}
