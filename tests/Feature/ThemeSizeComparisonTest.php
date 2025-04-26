<?php

namespace Tests\Feature;

use App\Models\Theme;
use App\Models\ThemeVersion;
use App\Models\ThemeVersionComparisonStat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeSizeComparisonTest extends TestCase
{
    use RefreshDatabase;

    public function test_size_comparison_chart_is_displayed()
    {
        $theme = Theme::factory()->create();
        $version1 = ThemeVersion::factory()->create([
            'theme_id' => $theme->id,
            'version' => '1.0.0',
            'total_size_kb' => 1024
        ]);
        $version2 = ThemeVersion::factory()->create([
            'theme_id' => $theme->id,
            'version' => '2.0.0',
            'total_size_kb' => 2048
        ]);

        $response = $this->get(route('themes.versions.compare', [
            'theme' => $theme,
            'baseVersion' => $version1->id,
            'targetVersion' => $version2->id
        ]));

        $response->assertStatus(200);
        $response->assertSee('Size Comparison Visualization');
        $response->assertSee('canvas id="sizeComparisonChart"');
        $response->assertSee('Version 1.0.0');
        $response->assertSee('Version 2.0.0');
        $response->assertSee('1024');
        $response->assertSee('2048');
    }

    public function test_api_compare_stats_endpoint()
    {
        $theme = Theme::factory()->create();
        $version1 = ThemeVersion::factory()->create(['theme_id' => $theme->id]);
        $version2 = ThemeVersion::factory()->create(['theme_id' => $theme->id]);

        $comparison = ThemeVersionComparisonStat::factory()->create([
            'theme_version_id' => $version1->id,
            'compared_with_version_id' => $version2->id,
            'total_size_kb' => 1024,
            'total_size_diff_kb' => 256,
            'css_size_kb' => 512,
            'css_size_diff_kb' => 128,
            'js_size_kb' => 384,
            'js_size_diff_kb' => -96,
            'image_size_kb' => 128,
            'image_size_diff_kb' => 32
        ]);

        $response = $this->actingAs($this->createUserWithPermission('view-themes'))
            ->getJson("/api/themes/{$theme->id}/versions/{$version1->id}/compare-stats/{$version2->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'files_added',
                    'files_removed',
                    'files_modified',
                    'lines_added',
                    'lines_removed',
                    'quality_score',
                    'complexity_change',
                    'coverage_change',
                    'performance_impact',
                    'size_metrics' => [
                        'total_size_kb',
                        'total_size_diff_kb',
                        'css_size_kb',
                        'css_size_diff_kb',
                        'js_size_kb',
                        'js_size_diff_kb',
                        'image_size_kb',
                        'image_size_diff_kb'
                    ]
                ]
            ]);
    }

    public function test_api_size_metrics_endpoint()
    {
        $theme = Theme::factory()->create();
        $version1 = ThemeVersion::factory()->create(['theme_id' => $theme->id]);
        $version2 = ThemeVersion::factory()->create(['theme_id' => $theme->id]);

        $comparison = ThemeVersionComparisonStat::factory()->create([
            'theme_version_id' => $version1->id,
            'compared_with_version_id' => $version2->id,
            'total_size_kb' => 1024,
            'total_size_diff_kb' => 256,
            'css_size_kb' => 512,
            'css_size_diff_kb' => 128,
            'js_size_kb' => 384,
            'js_size_diff_kb' => -96,
            'image_size_kb' => 128,
            'image_size_diff_kb' => 32
        ]);

        $response = $this->actingAs($this->createUserWithPermission('view-themes'))
            ->getJson("/api/themes/{$theme->id}/versions/{$version1->id}/size-metrics/{$version2->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'total_size_kb',
                    'total_size_diff_kb',
                    'css_size_kb',
                    'css_size_diff_kb',
                    'js_size_kb',
                    'js_size_diff_kb',
                    'image_size_kb',
                    'image_size_diff_kb',
                    'file_type_breakdown' => [
                        'css' => ['size', 'change'],
                        'js' => ['size', 'change'],
                        'images' => ['size', 'change']
                    ]
                ]
            ]);
    }

    protected function createUserWithPermission($permission)
    {
        $user = \App\Models\User::factory()->create();
        $user->givePermissionTo($permission);
        return $user;
    }
}
