<?php

namespace Tests\Feature;

use App\Models\ThemeVersionRollback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RollbackAnalyticsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_get_rollback_analytics()
    {
        $rollback = ThemeVersionRollback::factory()
            ->withVersions()
            ->completed()
            ->create();

        $response = $this->getJson("/api/theme-rollbacks/{$rollback->id}/analytics");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'completed_at',
                    'duration',
                    'version' => ['id'],
                    'rollback_to_version' => ['id']
                ]
            ]);
    }

    public function test_get_recent_rollbacks()
    {
        ThemeVersionRollback::factory()
            ->count(5)
            ->withVersions()
            ->create();

        $response = $this->getJson('/api/theme-rollbacks/recent');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'status',
                        'version_name',
                        'rollback_to_version_name'
                    ]
                ]
            ]);
    }

    public function test_get_success_rate()
    {
        $themeId = 1;
        ThemeVersionRollback::factory()
            ->count(3)
            ->withVersions(['theme_id' => $themeId])
            ->completed()
            ->create();

        ThemeVersionRollback::factory()
            ->count(2)
            ->withVersions(['theme_id' => $themeId])
            ->failed()
            ->create();

        $response = $this->getJson("/api/theme-rollbacks/{$themeId}/success-rate");

        $response->assertStatus(200)
            ->assertJson([
                'data' => ['success_rate' => 60.0]
            ]);
    }

    public function test_get_rollback_reasons()
    {
        $themeId = 2;
        ThemeVersionRollback::factory()
            ->count(3)
            ->withVersions(['theme_id' => $themeId])
            ->withReasons()
            ->create();

        $response = $this->getJson("/api/theme-rollbacks/{$themeId}/reasons");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'reason',
                        'count',
                        'percentage'
                    ]
                ]
            ]);
    }

    public function test_get_impact_analysis()
    {
        $themeId = 3;
        ThemeVersionRollback::factory()
            ->count(2)
            ->withVersions(['theme_id' => $themeId])
            ->withImpactData()
            ->create();

        $response = $this->getJson("/api/theme-rollbacks/{$themeId}/impact");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'performance_impact',
                    'stability_impact',
                    'user_satisfaction'
                ]
            ]);
    }

    public function test_get_user_behavior_patterns()
    {
        $themeId = 4;
        ThemeVersionRollback::factory()
            ->count(3)
            ->withVersions(['theme_id' => $themeId])
            ->withUserBehaviorData()
            ->create();

        $response = $this->getJson("/api/theme-rollbacks/{$themeId}/user-behavior");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'time_to_rollback',
                    'user_actions_before_rollback',
                    'affected_user_count'
                ]
            ]);
    }

    public function test_get_notification_preferences()
    {
        $themeId = 5;
        ThemeVersionRollback::factory()
            ->count(2)
            ->withVersions(['theme_id' => $themeId])
            ->withNotificationData()
            ->create();

        $response = $this->getJson("/api/theme-rollbacks/{$themeId}/notification-preferences");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'email_notifications',
                    'in_app_notifications',
                    'preferred_channels'
                ]
            ]);
    }

    public function test_unauthenticated_access_denied()
    {
        $this->app['auth']->logout();

        $response = $this->getJson('/api/theme-rollbacks/recent');
        $response->assertStatus(401);
    }

    public function test_invalid_rollback_id_returns_404()
    {
        $response = $this->getJson('/api/theme-rollbacks/999/analytics');
        $response->assertStatus(404);
    }

    public function test_invalid_theme_id_returns_empty_data()
    {
        $response = $this->getJson('/api/theme-rollbacks/999/reasons');
        $response->assertStatus(200)
            ->assertJson(['data' => []]);
    }
}
