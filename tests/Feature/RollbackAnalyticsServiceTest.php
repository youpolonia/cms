<?php

namespace Tests\Feature;

use App\Models\ThemeVersionRollback;
use App\Services\RollbackAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RollbackAnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    private RollbackAnalyticsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RollbackAnalyticsService();
    }

    public function test_get_rollback_stats()
    {
        $rollback = ThemeVersionRollback::factory()
            ->withVersions()
            ->completed()
            ->create();

        $stats = $this->service->getRollbackStats($rollback);

        $this->assertEquals($rollback->id, $stats['id']);
        $this->assertEquals('completed', $stats['status']);
        $this->assertNotNull($stats['completed_at']);
        $this->assertNotNull($stats['duration']);
        $this->assertEquals($rollback->version->id, $stats['version']['id']);
        $this->assertEquals($rollback->rollbackToVersion->id, $stats['rollback_to_version']['id']);
    }

    public function test_get_recent_rollbacks()
    {
        ThemeVersionRollback::factory()
            ->count(15)
            ->withVersions()
            ->sequence(
                ['status' => 'completed'],
                ['status' => 'failed'],
                ['status' => 'pending']
            )
            ->create();

        $recent = $this->service->getRecentRollbacks(10);

        $this->assertCount(10, $recent);
        $this->assertEquals('completed', $recent->first()['status']);
        $this->assertNotNull($recent->first()['version_name']);
        $this->assertNotNull($recent->first()['rollback_to_version_name']);
    }

    public function test_get_success_rate_with_no_rollbacks()
    {
        $rate = $this->service->getSuccessRate(1);
        $this->assertEquals(0, $rate);
    }

    public function test_get_success_rate_with_mixed_results()
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

        $rate = $this->service->getSuccessRate($themeId);
        $this->assertEquals(60.0, $rate);
    }

    public function test_get_success_rate_with_only_successful()
    {
        $themeId = 2;
        
        ThemeVersionRollback::factory()
            ->count(5)
            ->withVersions(['theme_id' => $themeId])
            ->completed()
            ->create();

        $rate = $this->service->getSuccessRate($themeId);
        $this->assertEquals(100.0, $rate);
    }

    public function test_get_success_rate_with_only_failed()
    {
        $themeId = 3;
        
        ThemeVersionRollback::factory()
            ->count(4)
            ->withVersions(['theme_id' => $themeId])
            ->failed()
            ->create();

        $rate = $this->service->getSuccessRate($themeId);
        $this->assertEquals(0.0, $rate);
    }
}
