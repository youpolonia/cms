<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_generation_with_valid_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->postJson('/api/content/generate', [
            'prompt' => 'Test content',
            'type' => 'generate',
            'content_type' => 'post',
            'tone' => 'professional',
            'length' => 'medium',
            'style' => 'detailed'
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'content',
                'usage' => [
                    'prompt_tokens',
                    'completion_tokens',
                    'total_tokens'
                ],
                'message'
            ]);
    }

    public function test_content_generation_without_token_fails()
    {
        $response = $this->postJson('/api/content/generate', [
            'prompt' => 'Test content',
            'type' => 'generate',
            'content_type' => 'post',
            'tone' => 'professional',
            'length' => 'medium',
            'style' => 'detailed'
        ]);

        $response->assertStatus(401);
    }
    public function test_content_generation_with_invalid_token_fails()
    {
        $response = $this->postJson('/api/content/generate', [
            'prompt' => 'Test content',
            'type' => 'generate',
            'content_type' => 'post',
            'tone' => 'professional',
            'length' => 'medium',
            'style' => 'detailed'
        ], [
            'Authorization' => 'Bearer invalid-token'
        ]);

        $response->assertStatus(401);
    }

    public function test_content_generation_with_missing_required_fields_fails()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->postJson('/api/content/generate', [
            // Missing required 'prompt' field
            'type' => 'generate',
            'content_type' => 'post',
            'tone' => 'professional'
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['prompt']);
    }

    public function test_content_generation_rate_limiting()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Make max allowed requests
        for ($i = 0; $i < 60; $i++) {
            $this->postJson('/api/content/generate', [
                'prompt' => 'Test content',
                'type' => 'generate',
                'content_type' => 'post',
                'tone' => 'professional',
                'length' => 'medium',
                'style' => 'detailed'
            ], [
                'Authorization' => 'Bearer ' . $token
            ]);
        }

        // Next request should be rate limited
        $response = $this->postJson('/api/content/generate', [
            'prompt' => 'Test content',
            'type' => 'generate',
            'content_type' => 'post',
            'tone' => 'professional',
            'length' => 'medium',
            'style' => 'detailed'
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(429);
    }
}
