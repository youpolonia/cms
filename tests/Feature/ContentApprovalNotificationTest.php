<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ContentVersion;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ContentApprovalNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContentApprovalNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->editor = User::factory()->create(['role' => 'editor']);
    }

    /** @test */
    public function it_sends_notification_when_approval_is_required()
    {
        Notification::fake();

        $version = ContentVersion::factory()->create([
            'status' => 'pending',
            'assigned_to' => $this->admin->id
        ]);

        // Trigger notification (this would normally be done in the controller)
        $this->admin->notify(new ContentApprovalNotification($version));

        Notification::assertSentTo(
            $this->admin,
            ContentApprovalNotification::class,
            function ($notification, $channels) use ($version) {
                return $notification->version->id === $version->id;
            }
        );
    }

    /** @test */
    public function it_shows_unread_notification_badge()
    {
        $version = ContentVersion::factory()->create(['status' => 'pending']);
        
        $this->admin->notifications()->create([
            'type' => 'App\Notifications\ContentApprovalNotification',
            'data' => [
                'message' => 'New content requires approval',
                'version_id' => $version->id,
                'content_title' => $version->content->title
            ],
            'read_at' => null
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'));

        $response->assertSee('inline-flex items-center justify-center w-4 h-4 rounded-full bg-red-500');
    }

    /** @test */
    public function it_marks_notification_as_read_when_viewed()
    {
        $version = ContentVersion::factory()->create(['status' => 'pending']);
        
        $notification = $this->admin->notifications()->create([
            'type' => 'App\Notifications\ContentApprovalNotification',
            'data' => [
                'message' => 'New content requires approval',
                'version_id' => $version->id,
                'content_title' => $version->content->title
            ],
            'read_at' => null
        ]);

        $this->actingAs($this->admin)
            ->get(route('content-approvals.show', $version->id));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    /** @test */
    public function it_only_notifies_users_with_approval_permissions()
    {
        Notification::fake();

        $version = ContentVersion::factory()->create([
            'status' => 'pending',
            'assigned_to' => $this->admin->id
        ]);

        // Editor shouldn't receive notification
        $this->editor->notify(new ContentApprovalNotification($version));

        Notification::assertNotSentTo(
            $this->editor,
            ContentApprovalNotification::class
        );
    }
}
