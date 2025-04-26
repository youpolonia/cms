<?php

namespace Tests\Feature;

use App\Models\Theme;
use App\Models\ThemeVersion;
use App\Models\ThemeVersionRollback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeRollbackApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_rollback_analytics()
    {
        $theme = Theme::factory()->create();
        $version1 = ThemeVersion::factory()->for($theme)->create();
        $version2 = ThemeVersion::factory()->for($theme)->create();
        
        ThemeVersionRollback::factory()
            ->count(3)
            ->for($version1)
            ->for($version2, 'rollbackToVersion')
            ->completed()
            ->create();

        ThemeVersionRollback::factory()
            ->for($version1)
            ->for($version2, 'rollbackToVersion')
            ->failed()
            ->create();

        $response = $this->getJson("/api/themes/{$theme->id}/rollbacks/analytics");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success_rate',
                'recent_rollbacks' => [
                    '*' => [
                        'id',
                        'status',
                        'created_at',
                        'version_name',
                        'rollback_to_version_name'
                    ]
                ]
            ])
            ->assertJsonPath('success_rate', 75.0)
            ->assertJsonCount(4, 'recent_rollbacks');
    }

    public function test_get_rollback_stats()
    {
        $theme = Theme::factory()->create();
        $version1 = ThemeVersion::factory()->for($theme)->create();
        $version2 = ThemeVersion::factory()->for($theme)->create();
        
        $rollback = ThemeVersionRollback::factory()
            ->for($version1)
            ->for($version2, 'rollbackToVersion')
            ->completed()
            ->create();

        $response = $this->getJson("/api/themes/{$theme->id}/rollbacks/{$rollback->id}/stats");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'status',
                'completed_at',
                'duration',
                'version' => ['id', 'name'],
                'rollback_to_version' => ['id', 'name'],
                'error_message'
            ])
            ->assertJsonPath('status', 'completed')
            ->assertJsonPath('version.id', $version1->id)
            ->assertJsonPath('rollback_to_version.id', $version2->id);
    }

    public function test_analytics_with_limit_parameter()
    {
        $theme = Theme::factory()->create();
        $version1 = ThemeVersion::factory()->for($theme)->create();
        $version2 = ThemeVersion::factory()->for($theme)->create();
        
        ThemeVersionRollback::factory()
            ->count(15)
            ->for($version1)
            ->for($version2, 'rollbackToVersion')
            ->create();

        $response = $this->getJson("/api/themes/{$theme->id}/rollbacks/analytics?limit=5");

        $response->assertStatus(200)
            ->assertJsonCount(5, 'recent_rollbacks');
    }

    public function test_nonexistent_rollback_stats()
    {
        $theme = Theme::factory()->create();
        
        $response = $this->getJson("/api/themes/{$theme->id}/rollbacks/999/stats");

        $response->assertStatus(404);
    }
}
