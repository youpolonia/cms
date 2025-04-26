<?php

namespace Tests\Feature;

use App\Models\ContentVersion;
use App\Models\ThemeVersion;
use App\Services\VersionComparison\SemanticDiffService;
use InvalidArgumentException;
use Tests\TestCase;

class SemanticDiffServiceTest extends TestCase
{
    private SemanticDiffService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SemanticDiffService();
    }

    public function test_compare_versions_with_theme_versions()
    {
        $oldVersion = ThemeVersion::factory()->create(['version' => '1.0.0']);
        $newVersion = ThemeVersion::factory()->create(['version' => '1.1.0']);

        $result = $this->service->compareVersions($oldVersion, $newVersion);

        $this->assertArrayHasKey('version_info', $result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('files', $result);
    }

    public function test_compare_versions_with_non_theme_versions_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        
        $oldVersion = ContentVersion::factory()->create();
        $newVersion = ContentVersion::factory()->create();
        
        $this->service->compareVersions($oldVersion, $newVersion);
    }

    public function test_get_version_info_returns_correct_structure()
    {
        $oldVersion = ThemeVersion::factory()->create(['version' => '1.0.0']);
        $newVersion = ThemeVersion::factory()->create(['version' => '2.0.0']);

        $result = $this->service->getVersionInfo($oldVersion, $newVersion);

        $this->assertEquals('1.0.0', $result['old_version']);
        $this->assertEquals('2.0.0', $result['new_version']);
        $this->assertEquals('major', $result['version_change']);
    }

    public function test_get_change_summary_counts_changes_correctly()
    {
        $oldVersion = ThemeVersion::factory()->create();
        $newVersion = ThemeVersion::factory()->create();

        $result = $this->service->getChangeSummary($oldVersion, $newVersion);

        $this->assertArrayHasKey('added_files', $result);
        $this->assertArrayHasKey('deleted_files', $result);
        $this->assertArrayHasKey('modified_files', $result);
        $this->assertArrayHasKey('semantic_changes', $result);
    }

    public function test_compare_files_returns_correct_structure()
    {
        $oldVersion = ThemeVersion::factory()->create();
        $newVersion = ThemeVersion::factory()->create();

        $result = $this->service->compareFiles($oldVersion, $newVersion);

        $this->assertArrayHasKey('added', $result);
        $this->assertArrayHasKey('deleted', $result);
        $this->assertArrayHasKey('modified', $result);
    }

    public function test_get_semantic_changes_returns_correct_structure()
    {
        $oldVersion = ThemeVersion::factory()->create();
        $newVersion = ThemeVersion::factory()->create();

        $result = $this->service->getSemanticChanges($oldVersion, $newVersion);

        $this->assertArrayHasKey('count', $result);
        $this->assertArrayHasKey('changes', $result);
    }

    public function test_generate_html_diff_returns_string()
    {
        $oldVersion = ThemeVersion::factory()->create();
        $newVersion = ThemeVersion::factory()->create();

        $result = $this->service->generateHtmlDiff($oldVersion, $newVersion);

        $this->assertIsString($result);
    }

    public function test_generate_css_diff_returns_string()
    {
        $oldVersion = ThemeVersion::factory()->create();
        $newVersion = ThemeVersion::factory()->create();

        $result = $this->service->generateCssDiff($oldVersion, $newVersion);

        $this->assertIsString($result);
    }

    public function test_generate_js_diff_returns_string()
    {
        $oldVersion = ThemeVersion::factory()->create();
        $newVersion = ThemeVersion::factory()->create();

        $result = $this->service->generateJsDiff($oldVersion, $newVersion);

        $this->assertIsString($result);
    }
}