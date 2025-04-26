<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationPreferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_enable_theme_comparison_notifications()
    {
        $user = User::factory()->create([
            'notification_preferences' => ['theme_comparisons' => false]
        ]);

        $response = $this->actingAs($user)
            ->post(route('profile.notification-settings.update'), [
                'theme_comparisons' => true
            ]);

        $response->assertRedirect();
        $this->assertTrue($user->fresh()->notification_preferences['theme_comparisons']);
    }

    public function test_user_can_disable_theme_comparison_notifications()
    {
        $user = User::factory()->create([
            'notification_preferences' => ['theme_comparisons' => true]
        ]);

        $response = $this->actingAs($user)
            ->post(route('profile.notification-settings.update'), [
                'theme_comparisons' => false
            ]);

        $response->assertRedirect();
        $this->assertFalse($user->fresh()->notification_preferences['theme_comparisons']);
    }

    public function test_notification_settings_page_loads()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('profile.notification-settings.edit'));

        $response->assertOk();
        $response->assertSee('Notification Preferences');
        $response->assertSee('Theme Comparison Notifications');
    }

    public function test_guest_cannot_access_notification_settings()
    {
        $response = $this->get(route('profile.notification-settings.edit'));
        $response->assertRedirect('/login');

        $response = $this->post(route('profile.notification-settings.update'));
        $response->assertRedirect('/login');
    }
}
