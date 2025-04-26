<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ContentVersionComparisonJob;
use App\Models\Content;
use App\Models\ContentVersion;
use Tests\TestCase;

class ContentVersionComparisonJobTest extends TestCase
{
    public function test_compares_text_content()
    {
        $content = Content::factory()->create();
        $version1 = ContentVersion::factory()->create([
            'content_id' => $content->id,
            'body' => 'Original content'
        ]);
        $version2 = ContentVersion::factory()->create([
            'content_id' => $content->id,
            'body' => 'Modified content'
        ]);

        $job = new ContentVersionComparisonJob($version1, $version2);
        $result = $job->handle();

        $this->assertArrayHasKey('diff', $result);
        $this->assertStringContainsString('Original', $result['diff']);
        $this->assertStringContainsString('Modified', $result['diff']);
    }

    public function test_compares_html_content()
    {
        $content = Content::factory()->create();
        $version1 = ContentVersion::factory()->create([
            'content_id' => $content->id,
            'body' => '<p>Original content</p>'
        ]);
        $version2 = ContentVersion::factory()->create([
            'content_id' => $content->id,
            'body' => '<p>Modified <strong>content</strong></p>'
        ]);

        $job = new ContentVersionComparisonJob($version1, $version2);
        $result = $job->handle();

        $this->assertArrayHasKey('html_diff', $result);
        $this->assertStringContainsString('Original', $result['html_diff']);
        $this->assertStringContainsString('Modified', $result['html_diff']);
    }

    public function test_handles_identical_content()
    {
        $content = Content::factory()->create();
        $version1 = ContentVersion::factory()->create([
            'content_id' => $content->id,
            'body' => 'Same content'
        ]);
        $version2 = ContentVersion::factory()->create([
            'content_id' => $content->id,
            'body' => 'Same content'
        ]);

        $job = new ContentVersionComparisonJob($version1, $version2);
        $result = $job->handle();

        $this->assertArrayHasKey('identical', $result);
        $this->assertTrue($result['identical']);
    }
}