<?php

namespace Tests\Feature;

use App\Models\Theme;
use App\Models\ThemeVersionRollback;
use App\Models\User;
use App\Notifications\ThemeRollbackNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ThemeRollbackNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_sent_to_configured_users()
    {
        Notification::fake();

        $user = User::factory()->create();
        $theme = Theme::factory()->create([
            'rollback_notifications_enabled' => true,
            'rollback_notification_users' => [$user->id]
        ]);

        $rollback = ThemeVersionRollback::factory()->create([
            'theme_id' => $theme->id
        ]);

        $theme->sendRollbackNotifications($rollback);

        Notification::assertSentTo(
            $user,
            ThemeRollbackNotification::class
        );
    }

    public function test_notification_not_sent_when_disabled()
    {
        Notification::fake();

        $user = User::factory()->create();
        $theme = Theme::factory()->create([
            'rollback_notifications_enabled' => false,
            'rollback_notification_users' => [$user->id]
        ]);

        $rollback = ThemeVersionRollback::factory()->create([
            'theme_id' => $theme->id
        ]);

        $theme->sendRollbackNotifications($rollback);

        Notification::assertNotSentTo(
            $user,
            ThemeRollbackNotification::class
        );
    }

    public function test_notification_sent_to_configured_roles()
    {
        Notification::fake();

        $role = \Spatie\Permission\Models\Role::create(['name' => 'admin']);
        $user = User::factory()->create()->assignRole('admin');
        
        $theme = Theme::factory()->create([
            'rollback_notifications_enabled' => true,
            'rollback_notification_roles' => [$role->id]
        ]);

        $rollback = ThemeVersionRollback::factory()->create([
            'theme_id' => $theme->id
        ]);

        $theme->sendRollbackNotifications($rollback);

        Notification::assertSentTo(
            $user,
            ThemeRollbackNotification::class
        );
    }
}
