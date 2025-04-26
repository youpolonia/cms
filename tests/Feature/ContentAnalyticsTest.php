<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContentAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_returns_analytics_data_for_authenticated_user()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/content-analytics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'metrics' => [
                        'totalViews',
                        'avgTimeOnPage',
                        'bounceRate',
                        'contentCount'
                    ],
                    'viewsData',
                    'topContentData'
                ],
                'range'
            ]);
    }

    /** @test */
    public function it_accepts_custom_range_parameter()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/content-analytics?range=30d');

        $response->assertStatus(200)
            ->assertJson(['range' => '30d']);
    }

    /** @test */
    public function it_returns_error_for_unauthenticated_users()
    {
        $response = $this->getJson('/api/content-analytics');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_handles_service_errors_gracefully()
    {
        Http::fake([
            '*' => Http::response([], 500)
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/content-analytics');

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Failed to fetch analytics data'
            ]);
    }
}