<?php

namespace Tests\Feature;

use App\Models\ContentVersionDiff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnalyticsExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_includes_version_comparison_metrics()
    {
        Storage::fake('exports');

        $user = User::factory()->create();
        $diff = ContentVersionDiff::factory()->create();

        $this->actingAs($user)
            ->post(route('content.export-analytics'))
            ->assertRedirect()
            ->assertSessionHas('success');

        // Simulate job completion
        $export = $user->analyticsExports()->create([
            'file_path' => 'exports/test.csv',
            'status' => 'completed'
        ]);

        Storage::put('exports/test.csv', 'test content');
        
        $response = $this->actingAs($user)
            ->get(route('exports.download', $export));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        // Verify export contains version comparison data
        $content = Storage::get('exports/test.csv');
        $this->assertStringContainsString('Version Comparisons', $content);
        $this->assertStringContainsString('Most Compared Version Pairs', $content);
    }

    public function test_json_export_includes_version_comparison_data()
    {
        Storage::fake('exports');

        $user = User::factory()->create();
        $diff = ContentVersionDiff::factory()->create();

        $this->actingAs($user)
            ->post(route('content.export-analytics'), [
                'type' => 'json'
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        // Simulate job completion
        $export = $user->analyticsExports()->create([
            'file_path' => 'exports/test.json',
            'status' => 'completed'
        ]);

        Storage::put('exports/test.json', json_encode([
            'version_comparisons' => [
                'total_comparisons' => 1,
                'most_compared_versions' => []
            ]
        ]));
        
        $response = $this->actingAs($user)
            ->get(route('exports.download', $export));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');

        $data = json_decode(Storage::get('exports/test.json'), true);
        $this->assertArrayHasKey('version_comparisons', $data);
        $this->assertEquals(1, $data['version_comparisons']['total_comparisons']);
    }
}
