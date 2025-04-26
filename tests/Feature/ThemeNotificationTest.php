<?php

namespace Tests\Feature;

use App\Models\Theme;
use App\Models\ThemeVersion;
use App\Models\User;
use App\Notifications\ThemeComparisonNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ThemeNotificationTest extends TestCase
{
    public function test_comparison_notification_sent_when_preference_enabled()
    {
        Notification::fake();

        $user = User::factory()->create([
            'notification_preferences' => ['theme_comparisons' => true]
        ]);

        $theme = Theme::factory()->create(['user_id' => $user->id]);
        $version1 = ThemeVersion::factory()->create(['theme_id' => $theme->id]);
        $version2 = ThemeVersion::factory()->create(['theme_id' => $theme->id]);

        // Trigger comparison
        $this->actingAs($user)
            ->post(route('themes.compare-versions', [
                'version1' => $version1->id,
                'version2' => $version2->id
            ]));

        Notification::assertSentTo(
            $user,
            ThemeComparisonNotification::class,
            function ($notification, $channels) {
                return in_array('mail', $channels) && 
                       in_array('database', $channels);
            }
        );
    }

    public function test_comparison_notification_not_sent_when_preference_disabled()
    {
        Notification::fake();

        $user = User::factory()->create([
            'notification_preferences' => ['theme_comparisons' => false]
        ]);

        $theme = Theme::factory()->create(['user_id' => $user->id]);
        $version1 = ThemeVersion::factory()->create(['theme_id' => $theme->id]);
        $version2 = ThemeVersion::factory()->create(['theme_id' => $theme->id]);

        // Trigger comparison
        $this->actingAs($user)
            ->post(route('themes.compare-versions', [
                'version1' => $version1->id,
                'version2' => $version2->id
            ]));

        Notification::assertNotSentTo($user, ThemeComparisonNotification::class);
    }

    public function test_email_content_contains_comparison_details()
    {
        $user = User::factory()->create([
            'notification_preferences' => ['theme_comparisons' => true]
        ]);

        $theme = Theme::factory()->create(['user_id' => $user->id]);
        $version1 = ThemeVersion::factory()->create([
            'theme_id' => $theme->id,
            'version_number' => '1.0.0'
        ]);
        $version2 = ThemeVersion::factory()->create([
            'theme_id' => $theme->id,
            'version_number' => '1.1.0'
        ]);

        $notification = new ThemeComparisonNotification([
            'theme_name' => $theme->name,
            'version_from' => $version1->version_number,
            'version_to' => $version2->version_number,
            'files_added' => 5,
            'files_removed' => 2,
            'files_modified' => 10,
            'lines_added' => 150,
            'lines_removed' => 75,
            'quality_score' => 85,
            'comparison_id' => 123
        ]);

        $mail = $notification->toMail($user);
        
        $this->assertStringContainsString($theme->name, $mail->render());
        $this->assertStringContainsString('1.0.0 → 1.1.0', $mail->render());
        $this->assertStringContainsString('Files Changed: 17', $mail->render());
        $this->assertStringContainsString('Lines Added: 150', $mail->render());
        $this->assertStringContainsString('Lines Removed: 75', $mail->render());
    }
}
