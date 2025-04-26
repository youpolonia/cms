<?php

namespace Tests\Feature;

use App\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class ProcessScheduledContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_publishes_scheduled_content()
    {
        $content = Content::factory()->create([
            'status' => Content::STATUS_DRAFT,
            'publish_at' => now()->subMinute(),
            'published_at' => null
        ]);

        Artisan::call('content:process-scheduled');

        $content->refresh();
        $this->assertEquals(Content::STATUS_PUBLISHED, $content->status);
        $this->assertNotNull($content->published_at);
    }

    public function test_archives_expired_content()
    {
        $content = Content::factory()->create([
            'status' => Content::STATUS_PUBLISHED,
            'expire_at' => now()->subMinute()
        ]);

        Artisan::call('content:process-scheduled');

        $content->refresh();
        $this->assertEquals(Content::STATUS_ARCHIVED, $content->status);
    }

    public function test_does_not_process_future_scheduled_content()
    {
        $content = Content::factory()->create([
            'status' => Content::STATUS_DRAFT,
            'publish_at' => now()->addHour()
        ]);

        Artisan::call('content:process-scheduled');

        $content->refresh();
        $this->assertEquals(Content::STATUS_DRAFT, $content->status);
    }

    public function test_outputs_processing_results()
    {
        $content = Content::factory()->create([
            'status' => Content::STATUS_DRAFT,
            'publish_at' => now()->subMinute(),
            'published_at' => null
        ]);

        $result = Artisan::call('content:process-scheduled');
        $this->assertEquals(0, $result);
        
        $content->refresh();
        $this->assertEquals(Content::STATUS_PUBLISHED, $content->status);
        $this->assertNotNull($content->published_at);
    }
}