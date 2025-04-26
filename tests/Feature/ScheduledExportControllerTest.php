<?php

namespace Tests\Feature;

use App\Models\AnalyticsExport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduledExportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function testScheduleExportSuccess()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/exports/schedule', [
                'name' => 'Weekly Report',
                'format' => 'csv',
                'schedule' => 'weekly',
                'metrics' => ['views', 'sessions']
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'export' => [
                    'name' => 'Weekly Report',
                    'status' => 'scheduled'
                ]
            ]);

        $this->assertDatabaseHas('analytics_exports', [
            'name' => 'Weekly Report',
            'status' => 'scheduled'
        ]);
    }

    public function testProcessScheduledExports()
    {
        $export = AnalyticsExport::factory()->create([
            'status' => 'scheduled',
            'schedule' => 'daily'
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/exports/process-scheduled');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('analytics_exports', [
            'id' => $export->id,
            'status' => 'processing'
        ]);
    }

    public function testScheduleExportValidation()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/exports/schedule', [
                'format' => 'invalid',
                'schedule' => 'invalid'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'format', 'schedule', 'metrics']);
    }

    public function testFailedScheduleProcessing()
    {
        $export = AnalyticsExport::factory()->create([
            'status' => 'scheduled',
            'schedule' => 'daily'
        ]);

        // Simulate failure by mocking a service to throw exception
        $this->mock(\App\Services\ExportScheduler::class, function ($mock) {
            $mock->shouldReceive('process')->andThrow(new \Exception('Processing failed'));
        });

        $response = $this->actingAs($this->user)
            ->postJson('/api/exports/process-scheduled');

        $response->assertStatus(500);

        $this->assertDatabaseHas('analytics_exports', [
            'id' => $export->id,
            'status' => 'failed'
        ]);
    }
}