<?php

namespace Tests\Unit\Services;

use App\Models\ContentVersion;
use App\Models\ThemeVersion;
use App\Services\ContentVersionComparisonService;
use Tests\TestCase;

class ContentVersionComparisonServiceTest extends TestCase
{
    private ContentVersionComparisonService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ContentVersionComparisonService();
    }

    public function test_compare_versions_with_scheduling_changes()
    {
        $version1 = ContentVersion::factory()->create([
            'publish_at' => '2025-01-01 00:00:00',
            'expire_at' => null,
            'status' => 'draft'
        ]);

        $version2 = ContentVersion::factory()->create([
            'publish_at' => '2025-01-15 00:00:00',
            'expire_at' => '2025-02-01 00:00:00',
            'status' => 'scheduled'
        ]);

        $result = $this->service->compare($version1, $version2);

        $this->assertArrayHasKey('publish_at', $result['metadata_changes']);
        $this->assertArrayHasKey('expire_at', $result['metadata_changes']);
        $this->assertArrayHasKey('status', $result['metadata_changes']);
    }

    public function test_semantic_changes_include_scheduling_messages()
    {
        $version1 = ContentVersion::factory()->create([
            'publish_at' => '2025-01-01 00:00:00',
            'expire_at' => null,
        ]);

        $version2 = ContentVersion::factory()->create([
            'publish_at' => '2025-01-15 00:00:00',
            'expire_at' => '2025-02-01 00:00:00',
        ]);

        $changes = $this->service->getSemanticChanges($version1, $version2);

        $this->assertContains(
            "Changed publish schedule from 2025-01-01 00:00:00 to 2025-01-15 00:00:00",
            $changes
        );
        $this->assertContains(
            "Set expiration schedule to 2025-02-01 00:00:00",
            $changes
        );
    }

    public function test_compare_files()
    {
        $version1 = ThemeVersion::factory()->create();
        $version2 = ThemeVersion::factory()->create();
        
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, "Hello world");
        
        $version1->addFile('test.txt', $tempFile);
        file_put_contents($tempFile, "Hello there world");
        $version2->addFile('test.txt', $tempFile);
        
        $result = $this->service->compareFiles($version1, $version2, ['test.txt']);
        
        $this->assertArrayHasKey('test.txt', $result);
        $this->assertArrayHasKey('file_changes', $result['test.txt']);
        $this->assertArrayHasKey('version_numbers', $result['test.txt']);
        $this->assertCount(1, $result['test.txt']['file_changes']);
        $this->assertEquals('added', $result['test.txt']['file_changes'][0]['type']);
        
        unlink($tempFile);
    }

    public function test_compare_files_with_nonexistent_file()
    {
        $version1 = ThemeVersion::factory()->create();
        $version2 = ThemeVersion::factory()->create();
        
        $this->expectException(\InvalidArgumentException::class);
        $this->service->compareFiles($version1, $version2, ['nonexistent.txt']);
    }

    public function test_compare_versions_with_no_scheduling_changes()
    {
        $version1 = ContentVersion::factory()->create([
            'publish_at' => '2025-01-01 00:00:00',
            'expire_at' => '2025-02-01 00:00:00',
        ]);

        $version2 = ContentVersion::factory()->create([
            'publish_at' => '2025-01-01 00:00:00',
            'expire_at' => '2025-02-01 00:00:00',
        ]);

        $result = $this->service->compare($version1, $version2);
        $this->assertArrayNotHasKey('publish_at', $result['metadata_changes']);
        $this->assertArrayNotHasKey('expire_at', $result['metadata_changes']);
    }
}