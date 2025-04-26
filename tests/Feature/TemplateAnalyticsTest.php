<?php

namespace Tests\Feature;

use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_tracks_template_usage()
    {
        $user = User::factory()->create();
        $template = Template::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/templates/analytics', [
                'template_id' => $template->id,
                'action' => 'applied'
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Template usage tracked'
            ]);
    }

    public function test_gets_template_stats()
    {
        $template = Template::factory()->create();
        $users = User::factory()->count(3)->create();

        // Create test analytics
        foreach ($users as $user) {
            $template->analytics()->create([
                'user_id' => $user->id,
                'action' => 'applied'
            ]);
        }

        $response = $this->getJson("/api/templates/analytics/{$template->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'total_uses' => 3,
                    'applied_count' => 3,
                    'unique_users' => 3
                ]
            ]);
    }
}