<?php

namespace Tests\Feature;

use App\Models\AnalyticsExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnalyticsExportCleanupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('exports');
    }

    /** @test */
    public function it_deletes_expired_exports()
    {
        config(['analytics.export_retention_days' => 7]);

        $expiredExport = AnalyticsExport::factory()->create([
            'expires_at' => now()->subDay(),
            'file_path' => 'exports/expired.csv'
        ]);
        Storage::put('exports/expired.csv', 'test data');

        $this->artisan('analytics:cleanup-exports')
            ->expectsOutput('Deleted 1 expired analytics exports.');

        $this->assertDatabaseMissing('analytics_exports', ['id' => $expiredExport->id]);
        Storage::assertMissing('exports/expired.csv');
    }

    /** @test */
    public function it_deletes_exports_older_than_retention_period()
    {
        config(['analytics.export_retention_days' => 7]);

        $oldExport = AnalyticsExport::factory()->create([
            'created_at' => now()->subDays(8),
            'file_path' => 'exports/old.csv'
        ]);
        Storage::put('exports/old.csv', 'test data');

        $this->artisan('analytics:cleanup-exports')
            ->expectsOutput('Deleted 1 expired analytics exports.');

        $this->assertDatabaseMissing('analytics_exports', ['id' => $oldExport->id]);
        Storage::assertMissing('exports/old.csv');
    }

    /** @test */
    public function it_skips_active_exports()
    {
        config(['analytics.export_retention_days' => 7]);

        $activeExport = AnalyticsExport::factory()->create([
            'expires_at' => now()->addDay(),
            'created_at' => now()->subDays(6),
            'file_path' => 'exports/active.csv'
        ]);
        Storage::put('exports/active.csv', 'test data');

        $this->artisan('analytics:cleanup-exports')
            ->expectsOutput('Deleted 0 expired analytics exports.');

        $this->assertDatabaseHas('analytics_exports', ['id' => $activeExport->id]);
        Storage::assertExists('exports/active.csv');
    }

    /** @test */
    public function it_handles_disabled_cleanup()
    {
        config(['analytics.export_retention_days' => null]);

        $export = AnalyticsExport::factory()->create([
            'expires_at' => now()->subDay(),
            'file_path' => 'exports/disabled.csv'
        ]);
        Storage::put('exports/disabled.csv', 'test data');

        $this->artisan('analytics:cleanup-exports')
            ->expectsOutput('Export cleanup is disabled (retention period set to null).');

        $this->assertDatabaseHas('analytics_exports', ['id' => $export->id]);
        Storage::assertExists('exports/disabled.csv');
    }
}
