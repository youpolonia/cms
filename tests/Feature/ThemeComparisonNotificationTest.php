<?php

namespace Tests\Feature;

use App\Models\ThemeVersionComparisonStat;
use App\Models\User;
use App\Notifications\ThemeComparisonNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ThemeComparisonNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_is_sent_when_comparison_exceeds_threshold()
    {
        Notification::fake();

        $user = User::factory()->create();
        $comparisonStats = ThemeVersionComparisonStat::factory()->create([
            'change_percentage' => 75
        ]);

        $user->notify(new ThemeComparisonNotification($comparisonStats));

        Notification::assertSentTo(
            $user,
            ThemeComparisonNotification::class,
            function ($notification) use ($comparisonStats) {
                return $notification->comparisonStats->id === $comparisonStats->id;
            }
        );
    }

    public function test_notification_contains_correct_content()
    {
        $comparisonStats = ThemeVersionComparisonStat::factory()->create([
            'change_percentage' => 60,
            'files_changed' => 42
        ]);

        $notification = new ThemeComparisonNotification($comparisonStats);
        $mail = $notification->toMail($comparisonStats);

        $this->assertEquals('Theme Version Comparison Results', $mail->subject);
        $this->assertStringContainsString('60%', $mail->render());
        $this->assertStringContainsString('42', $mail->render());
    }

    public function test_notification_is_queued()
    {
        $comparisonStats = ThemeVersionComparisonStat::factory()->create();
        $notification = new ThemeComparisonNotification($comparisonStats);

        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $notification);
    }
}
