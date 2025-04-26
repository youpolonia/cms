<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Theme;
use App\Models\ThemeVersion;
use App\Models\ThemeApprovalWorkflow;
use App\Notifications\ThemeApprovalNotification;
use Illuminate\Support\Facades\Notification;

class ThemeApprovalNotificationTest extends TestCase
{
    public function test_approved_notification_sent()
    {
        Notification::fake();

        $user = User::factory()->create();
        $theme = Theme::factory()->create(['user_id' => $user->id]);
        $workflow = ThemeApprovalWorkflow::factory()->create(['theme_id' => $theme->id]);
        $version = ThemeVersion::factory()->create(['theme_id' => $theme->id]);

        $user->notify(new ThemeApprovalNotification(
            themeName: $theme->name,
            version: $version->version_number,
            status: 'approved',
            actorName: 'Test Approver',
            comments: 'Great work!',
            nextSteps: 'Your theme is now live',
            includeComments: true,
            includeNextSteps: true
        ));

        Notification::assertSentTo(
            $user,
            ThemeApprovalNotification::class,
            function ($notification) use ($theme, $version) {
                return $notification->themeName === $theme->name &&
                       $notification->version === $version->version_number &&
                       $notification->status === 'approved';
            }
        );
    }

    public function test_rejected_notification_sent()
    {
        Notification::fake();

        $user = User::factory()->create();
        $theme = Theme::factory()->create(['user_id' => $user->id]);
        $workflow = ThemeApprovalWorkflow::factory()->create(['theme_id' => $theme->id]);
        $version = ThemeVersion::factory()->create(['theme_id' => $theme->id]);

        $user->notify(new ThemeApprovalNotification(
            themeName: $theme->name,
            version: $version->version_number,
            status: 'rejected',
            actorName: 'Test Reviewer',
            comments: 'Needs more work',
            rejectionReason: 'Incomplete documentation',
            nextSteps: 'Please update and resubmit',
            includeComments: true,
            includeNextSteps: true
        ));

        Notification::assertSentTo(
            $user,
            ThemeApprovalNotification::class,
            function ($notification) use ($theme, $version) {
                return $notification->themeName === $theme->name &&
                       $notification->version === $version->version_number &&
                       $notification->status === 'rejected';
            }
        );
    }

    public function test_notification_respects_preferences()
    {
        Notification::fake();

        $user = User::factory()->create([
            'notification_preferences' => [
                'theme_approval' => [
                    'enabled' => false,
                    'includeComments' => false,
                    'includeNextSteps' => false
                ]
            ]
        ]);

        $theme = Theme::factory()->create(['user_id' => $user->id]);
        $version = ThemeVersion::factory()->create(['theme_id' => $theme->id]);

        $user->notify(new ThemeApprovalNotification(
            themeName: $theme->name,
            version: $version->version_number,
            status: 'approved',
            actorName: 'Test Approver',
            comments: 'Should not be included',
            nextSteps: 'Should not be included',
            includeComments: false,
            includeNextSteps: false
        ));

        Notification::assertNotSentTo($user, ThemeApprovalNotification::class);
    }
}
