<?php

namespace Tests\Unit\Models;

use App\Models\Theme;
use App\Models\ThemeVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeVersionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_theme_version()
    {
        $version = ThemeVersion::factory()->create();
        
        $this->assertNotNull($version);
        $this->assertInstanceOf(ThemeVersion::class, $version);
    }

    public function test_it_belongs_to_theme()
    {
        $version = ThemeVersion::factory()->create();
        
        $this->assertInstanceOf(Theme::class, $version->theme);
    }

    public function test_it_can_have_parent_version()
    {
        $parent = ThemeVersion::factory()->create();
        $child = ThemeVersion::factory()->withParent()->create(['parent_version_id' => $parent->id]);
        
        $this->assertInstanceOf(ThemeVersion::class, $child->parentVersion);
        $this->assertEquals($parent->id, $child->parentVersion->id);
    }

    public function test_it_can_have_branches()
    {
        $parent = ThemeVersion::factory()->create();
        $branch = ThemeVersion::factory()->asBranch('feature-x')->create(['parent_version_id' => $parent->id]);
        
        $this->assertCount(1, $parent->branches);
        $this->assertEquals('feature-x', $branch->branch_name);
    }

    public function test_it_can_check_if_main_branch()
    {
        $main = ThemeVersion::factory()->create(['branch_name' => null]);
        $branch = ThemeVersion::factory()->asBranch('feature-x')->create();
        
        $this->assertTrue($main->isMainBranch());
        $this->assertFalse($branch->isMainBranch());
    }

    public function test_it_can_get_tag_list()
    {
        $version = ThemeVersion::factory()->withTags(['stable', 'release'])->create();
        
        $this->assertEquals(['stable', 'release'], $version->tag_list);
    }

    public function test_manifest_is_cast_to_array()
    {
        $version = ThemeVersion::factory()->create();
        
        $this->assertIsArray($version->manifest);
    }
}