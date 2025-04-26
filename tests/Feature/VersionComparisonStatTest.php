<?php

namespace Tests\Feature;

use App\Models\ContentVersion;
use App\Models\VersionComparisonStat;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VersionComparisonStatTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure we're using SQLite in-memory for tests
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
        
        // Run migrations once at the start
        if (!Schema::hasTable('version_comparison_stats')) {
            $this->artisan('migrate');
        }
    }

    public function test_stat_can_be_created()
    {
        $version = ContentVersion::factory()->create();
        $stat = VersionComparisonStat::factory()->create([
            'version_id' => $version->id
        ]);

        $this->assertDatabaseHas('version_comparison_stats', [
            'id' => $stat->id,
            'version_id' => $version->id
        ]);
    }

    public function test_stat_belongs_to_version()
    {
        $version = ContentVersion::factory()->create();
        $stat = VersionComparisonStat::factory()->create([
            'version_id' => $version->id
        ]);

        $this->assertEquals($version->id, $stat->version->id);
    }

    public function test_stat_has_change_summary()
    {
        $stat = VersionComparisonStat::factory()->create();
        
        $this->assertIsArray($stat->change_summary);
        $this->assertArrayHasKey('added', $stat->change_summary);
        $this->assertArrayHasKey('removed', $stat->change_summary);
        $this->assertArrayHasKey('modified', $stat->change_summary);
    }

    public function test_stat_can_be_updated()
    {
        $stat = VersionComparisonStat::factory()->create();
        $newLines = 50;

        $stat->update(['lines_added' => $newLines]);

        $this->assertEquals($newLines, $stat->fresh()->lines_added);
    }

    public function test_stat_can_be_deleted()
    {
        $stat = VersionComparisonStat::factory()->create();
        $statId = $stat->id;

        $stat->delete();

        $this->assertDatabaseMissing('version_comparison_stats', [
            'id' => $statId
        ]);
    }
}
