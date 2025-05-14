<?php

namespace Tests\Feature;

use App\Models\AnalyticsExport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnalyticsExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('exports');
        Queue::fake();
    }

    public function test_user_can_create_content_metrics_export()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->postJson('/api/analytics/exports/content-metrics', [
                'format' => 'csv',
                'date_range' => [
                    'start' => '2025-01-01',
                    'end' => '2025-01-31'
                ]
            ]);

        $response->assertStatus(202)
            ->assertJsonStructure(['message', 'export_id']);
        
        $this->assertDatabaseHas('analytics_exports', [
            'type' => 'content_metrics',
            'format' => 'csv',
            'status' => 'pending'
        ]);
    }

    public function test_export_processing_creates_file()
    {
        Storage::fake('exports');
        
        $export = AnalyticsExport::factory()->create([
            'type' => 'content_metrics',
            'format' => 'csv'
        ]);

        $job = new \App\Jobs\ProcessAnalyticsExport($export);
        $job->handle();

        Storage::disk('exports')->assertExists($export->file_path);
        $this->assertEquals('completed', $export->fresh()->status);
    }

    public function test_user_can_download_export()
    {
        $user = User::factory()->create();
        $export = AnalyticsExport::factory()->create([
            'user_id' => $user->id,
            'file_path' => 'exports/test.csv',
            'status' => 'completed'
        ]);

        Storage::fake('exports');
        Storage::disk('exports')->put('exports/test.csv', 'test data');

        $response = $this->actingAs($user)
            ->get("/api/analytics/exports/{$export->id}/download");

        $response->assertOk();
    }

    public function test_user_can_schedule_recurring_export()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->postJson('/api/analytics/exports/schedule', [
                'type' => 'content_metrics',
                'format' => 'csv',
                'frequency' => 'weekly',
                'send_email' => true
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('analytics_exports', [
            'is_scheduled' => true,
            'frequency' => 'weekly'
        ]);
    }

    public function test_export_status_endpoint_works()
    {
        $user = User::factory()->create();
        $export = AnalyticsExport::factory()->create([
            'user_id' => $user->id,
            'progress' => 50
        ]);

        $response = $this->actingAs($user)
            ->get("/api/analytics/exports/{$export->id}/status");

        $response->assertOk()
            ->assertJsonStructure(['status', 'progress']);
    }
}