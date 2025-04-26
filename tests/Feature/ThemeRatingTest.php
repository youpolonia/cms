<?php

namespace Tests\Feature;

use App\Models\Theme;
use App\Models\ThemeRating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeRatingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_rate_theme()
    {
        $theme = Theme::factory()->create();

        $response = $this->actingAs($this->user)
            ->postJson("/api/themes/{$theme->id}/ratings", [
                'rating' => 4,
                'review' => 'Great theme!'
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'rating' => ['id', 'rating', 'review'],
                'average_rating',
                'rating_count'
            ]);

        $this->assertDatabaseHas('theme_ratings', [
            'theme_id' => $theme->id,
            'user_id' => $this->user->id,
            'rating' => 4,
            'review' => 'Great theme!'
        ]);
    }

    public function test_user_can_update_rating()
    {
        $theme = Theme::factory()->create();
        $rating = ThemeRating::factory()->create([
            'theme_id' => $theme->id,
            'user_id' => $this->user->id,
            'rating' => 3
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/themes/{$theme->id}/ratings", [
                'rating' => 5,
                'review' => 'Updated review'
            ]);

        $response->assertStatus(201);
        $this->assertEquals(5, $rating->fresh()->rating);
        $this->assertEquals('Updated review', $rating->fresh()->review);
    }

    public function test_can_get_theme_ratings()
    {
        $theme = Theme::factory()->create();
        ThemeRating::factory(5)->create(['theme_id' => $theme->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/themes/{$theme->id}/ratings");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'ratings' => ['*' => ['id', 'rating', 'review', 'user']],
                'average_rating',
                'rating_count',
                'user_rating'
            ]);
    }

    public function test_can_get_user_rating()
    {
        $theme = Theme::factory()->create();
        $rating = ThemeRating::factory()->create([
            'theme_id' => $theme->id,
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/themes/{$theme->id}/ratings/user");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $rating->id,
                'rating' => $rating->rating
            ]);
    }

    public function test_can_delete_rating()
    {
        $theme = Theme::factory()->create();
        ThemeRating::factory()->create([
            'theme_id' => $theme->id,
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/themes/{$theme->id}/ratings");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'average_rating',
                'rating_count'
            ]);

        $this->assertDatabaseMissing('theme_ratings', [
            'theme_id' => $theme->id,
            'user_id' => $this->user->id
        ]);
    }

    public function test_rating_validation()
    {
        $theme = Theme::factory()->create();

        $response = $this->actingAs($this->user)
            ->postJson("/api/themes/{$theme->id}/ratings", [
                'rating' => 6, // Invalid
                'review' => str_repeat('a', 1001) // Too long
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating', 'review']);
    }
}
