<?php

namespace Tests\Feature;

use App\Models\Theme;
use App\Models\ThemeVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeVersionComparisonTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_compare_two_versions()
    {
        $user = User::factory()->create();
        $theme = Theme::factory()->create(['user_id' => $user->id]);
        $version1 = ThemeVersion::factory()->create(['theme_id' => $theme->id]);
        $version2 = ThemeVersion::factory()->create(['theme_id' => $theme->id]);

        $response = $this->actingAs($user)
            ->post(route('themes.compare-versions', [
                'version1' => $version1->id,
                'version2' => $version2->id
            ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'comparison_id',
            'files_added',
            'files_removed',
            'files_modified',
            'lines_added',
            'lines_removed',
            'quality_score'
        ]);
    }

    public function test_cannot_compare_versions_from_different_themes()
    {
        $user = User::factory()->create();
        $theme1 = Theme::factory()->create(['user_id' => $user->id]);
        $theme2 = Theme::factory()->create(['user_id' => $user->id]);
        $version1 = ThemeVersion::factory()->create(['theme_id' => $theme1->id]);
        $version2 = ThemeVersion::factory()->create(['theme_id' => $theme2->id]);

        $response = $this->actingAs($user)
            ->post(route('themes.compare-versions', [
                'version1' => $version1->id,
                'version2' => $version2->id
            ]));

        $response->assertStatus(422);
    }

    public function test_guest_cannot_compare_versions()
    {
        $theme = Theme::factory()->create();
        $version1 = ThemeVersion::factory()->create(['theme_id' => $theme->id]);
        $version2 = ThemeVersion::factory()->create(['theme_id' => $theme->id]);

        $response = $this->post(route('themes.compare-versions', [
            'version1' => $version1->id,
            'version2' => $version2->id
        ]));

        $response->assertRedirect('/login');
    }

    public function test_can_view_comparison_results()
    {
        $user = User::factory()->create();
        $theme = Theme::factory()->create(['user_id' => $user->id]);
        $version1 = ThemeVersion::factory()->create(['theme_id' => $theme->id]);
        $version2 = ThemeVersion::factory()->create(['theme_id' => $theme->id]);

        $response = $this->actingAs($user)
            ->get(route('themes.view-comparison', [
                'version1' => $version1->id,
                'version2' => $version2->id
            ]));

        $response->assertOk();
        $response->assertSee($version1->version_number);
        $response->assertSee($version2->version_number);
    }
}
