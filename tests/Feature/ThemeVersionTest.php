<?php

namespace Tests\Feature;

use App\Models\Theme;
use App\Models\ThemeVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ThemeVersionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $theme;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->theme = Theme::factory()->create(['user_id' => $this->user->id]);
        
        // Create initial version
        ThemeVersion::factory()->create([
            'theme_id' => $this->theme->id,
            'version' => '1.0.0',
            'is_active' => true
        ]);
    }

    public function test_user_can_create_new_version()
    {
        $this->actingAs($this->user)
            ->post(route('themes.versions.store', $this->theme), [
                'version' => '1.1.0',
                'description' => 'New features',
                'changelog' => ['Added new components']
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('theme_versions', [
            'theme_id' => $this->theme->id,
            'version' => '1.1.0'
        ]);
    }

    public function test_user_can_compare_versions()
    {
        $version1 = ThemeVersion::factory()->create([
            'theme_id' => $this->theme->id,
            'version' => '1.0.1'
        ]);

        $version2 = ThemeVersion::factory()->create([
            'theme_id' => $this->theme->id,
            'version' => '1.0.2'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('themes.versions.compare', [
                $this->theme,
                $version1,
                $version2
            ]));

        $response->assertOk();
        $response->assertViewHas('diff');
    }

    public function test_user_can_rollback_to_previous_version()
    {
        $version = ThemeVersion::factory()->create([
            'theme_id' => $this->theme->id,
            'version' => '1.0.3'
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('themes.versions.rollback', [
                $this->theme,
                $version
            ]), [
                'confirm' => true,
                'notes' => 'Rolling back due to issues'
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('theme_versions', [
            'version' => '1.0.3-rollback'
        ]);
    }

    public function test_user_can_view_version_history()
    {
        ThemeVersion::factory()->count(5)->create([
            'theme_id' => $this->theme->id,
            'version' => $this->faker->unique()->semver()
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('themes.versions.history', $this->theme));

        $response->assertOk();
        $response->assertViewHas('versions');
    }

    public function test_user_can_download_version()
    {
        Storage::fake('theme-exports');
        
        $version = ThemeVersion::factory()->create([
            'theme_id' => $this->theme->id,
            'version' => '1.0.4'
        ]);

        $filename = "theme-{$this->theme->slug}-v1.0.4.zip";
        Storage::disk('theme-exports')->put($filename, 'test content');

        $response = $this->actingAs($this->user)
            ->get(route('themes.versions.download', [
                $this->theme,
                $version
            ]));

        $response->assertDownload($filename);
    }

    public function test_user_can_add_and_remove_tags()
    {
        $version = ThemeVersion::factory()->create([
            'theme_id' => $this->theme->id,
            'version' => '1.0.5'
        ]);

        // Add tag
        $this->actingAs($this->user)
            ->post(route('themes.versions.tags.add', $version), [
                'tag' => 'stable'
            ])
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('theme_versions', [
            'id' => $version->id,
            'tags' => json_encode(['stable'])
        ]);

        // Remove tag
        $this->actingAs($this->user)
            ->post(route('themes.versions.tags.remove', $version), [
                'tag' => 'stable'
            ])
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('theme_versions', [
            'id' => $version->id,
            'tags' => json_encode([])
        ]);
    }

    public function test_theme_size_comparison_component_renders_correctly()
    {
        $version1 = ThemeVersion::factory()->create([
            'theme_id' => $this->theme->id,
            'version' => '1.1.0',
            'total_size_kb' => 1024,
            'assets_size_kb' => 512,
            'templates_size_kb' => 256,
            'scripts_size_kb' => 128,
            'styles_size_kb' => 128
        ]);

        $version2 = ThemeVersion::factory()->create([
            'theme_id' => $this->theme->id,
            'version' => '1.2.0',
            'total_size_kb' => 2048,
            'assets_size_kb' => 1024,
            'templates_size_kb' => 512,
            'scripts_size_kb' => 256,
            'styles_size_kb' => 256
        ]);

        $view = $this->blade(
            '<x-theme-size-comparison :version1="$version1" :version2="$version2" />',
            ['version1' => $version1, 'version2' => $version2]
        );

        $view->assertSee('Size Comparison (KB)');
        $view->assertSee('1.1.0');
        $view->assertSee('1.2.0');
        $view->assertSee('1,024.00');
        $view->assertSee('2,048.00');
        $view->assertSee('+1,024.00');
    }

    public function test_theme_size_comparison_shows_in_version_compare_view()
    {
        $version1 = ThemeVersion::factory()->create([
            'theme_id' => $this->theme->id,
            'version' => '1.1.0'
        ]);

        $version2 = ThemeVersion::factory()->create([
            'theme_id' => $this->theme->id,
            'version' => '1.2.0'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('themes.versions.compare', [
                $this->theme,
                $version1,
                $version2
            ]));

        $response->assertSee('Size Comparison (KB)');
    }
}
