<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Cache;
use App\Jobs\GenerateContentJob;
use App\Jobs\ImproveContentJob;
use App\Jobs\GenerateSummaryJob;
use App\Jobs\GenerateSEOJob;

class AsyncJobProcessingTest extends TestCase
{
    public function test_content_generation_job()
    {
        Queue::fake();
        
        $cacheKey = 'test-content-' . uniqid();
        $job = new GenerateContentJob('Test prompt', 'gpt-4', $cacheKey);
        
        $job->handle();
        
        $this->assertTrue(Cache::has($cacheKey));
    }

    public function test_content_improvement_job()
    {
        Queue::fake();
        
        $cacheKey = 'test-improve-' . uniqid();
        $job = new ImproveContentJob('Test content', 'Make it better', 'gpt-4', $cacheKey);
        
        $job->handle();
        
        $this->assertTrue(Cache::has($cacheKey));
    }

    public function test_summary_generation_job()
    {
        Queue::fake();
        
        $cacheKey = 'test-summary-' . uniqid();
        $job = new GenerateSummaryJob('Test content', 'gpt-4', 100, $cacheKey);
        
        $job->handle();
        
        $this->assertTrue(Cache::has($cacheKey));
    }

    public function test_seo_generation_job()
    {
        Queue::fake();
        
        $cacheKey = 'test-seo-' . uniqid();
        $job = new GenerateSEOJob('Test content', 'gpt-4', 'keyword', 'professional', $cacheKey);
        
        $job->handle();
        
        $this->assertTrue(Cache::has($cacheKey));
    }

    public function test_job_status_api()
    {
        $cacheKey = 'test-status-' . uniqid();
        Cache::put($cacheKey, ['test' => 'data'], 3600);
        
        $response = $this->getJson("/api/jobs/status?cache_key=$cacheKey");
        
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'completed',
                'result' => ['test' => 'data']
            ]);
    }
}