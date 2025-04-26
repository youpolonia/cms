<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteVerificationTest extends TestCase
{
    /** @test */
    public function analytics_route_exists_and_is_accessible()
    {
        // Verify route exists in the route collection
        $this->assertTrue(Route::has('content.analytics'));

        // Create and authenticate test user
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        // Test actual access to the route
        $response = $this->get('/content/analytics');
        $response->assertStatus(200);
        $response->assertViewIs('content.analytics');
    }
}
