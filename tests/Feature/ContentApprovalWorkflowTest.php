<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalStep;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ContentApprovalNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContentApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->editor = User::factory()->create(['role' => 'editor']);
        
        // Create a simple approval workflow
        $this->workflow = ApprovalWorkflow::factory()->create([
            'name' => 'Content Approval',
            'model_type' => ContentVersion::class
        ]);
        
        // Create workflow steps
        $this->step1 = ApprovalStep::factory()->create([
            'workflow_id' => $this->workflow->id,
            'name' => 'Editor Review',
            'role' => 'editor',
            'order' => 1
        ]);
        
        $this->step2 = ApprovalStep::factory()->create([
            'workflow_id' => $this->workflow->id,
            'name' => 'Admin Approval',
            'role' => 'admin',
            'order' => 2
        ]);
    }

    /** @test */
    public function it_assigns_to_first_step_when_content_is_submitted()
    {
        $content = Content::factory()->create();
        $version = ContentVersion::factory()->create([
            'content_id' => $content->id,
            'status' => 'pending'
        ]);

        $this->assertEquals(1, $version->current_step);
        $this->assertEquals('editor', $version->assigned_to_role);
    }

    /** @test */
    public function it_progresses_to_next_step_when_approved()
    {
        $content = Content::factory()->create();
        $version = ContentVersion::factory()->create([
            'content_id' => $content->id,
            'status' => 'pending',
            'current_step' => 1,
            'assigned_to_role' => 'editor'
        ]);

        // Editor approves
        $response = $this->actingAs($this->editor)
            ->post(route('content-approvals.approve', $version->id));

        $version->refresh();
        $this->assertEquals(2, $version->current_step);
        $this->assertEquals('admin', $version->assigned_to_role);
    }

    /** @test */
    public function it_completes_when_final_step_is_approved()
    {
        $content = Content::factory()->create();
        $version = ContentVersion::factory()->create([
            'content_id' => $content->id,
            'status' => 'pending',
            'current_step' => 2,
            'assigned_to_role' => 'admin'
        ]);

        // Admin approves
        $response = $this->actingAs($this->admin)
            ->post(route('content-approvals.approve', $version->id));

        $version->refresh();
        $this->assertEquals('approved', $version->status);
        $this->assertNotNull($version->approved_at);
    }

    /** @test */
    public function it_sends_notification_when_moved_to_next_step()
    {
        Notification::fake();

        $content = Content::factory()->create();
        $version = ContentVersion::factory()->create([
            'content_id' => $content->id,
            'status' => 'pending',
            'current_step' => 1,
            'assigned_to_role' => 'editor'
        ]);

        // Editor approves (should notify admin)
        $this->actingAs($this->editor)
            ->post(route('content-approvals.approve', $version->id));

        Notification::assertSentTo(
            $this->admin,
            ContentApprovalNotification::class
        );
    }

    /** @test */
    public function it_rejects_content_when_any_step_rejects()
    {
        $content = Content::factory()->create();
        $version = ContentVersion::factory()->create([
            'content_id' => $content->id,
            'status' => 'pending',
            'current_step' => 1,
            'assigned_to_role' => 'editor'
        ]);

        // Editor rejects
        $response = $this->actingAs($this->editor)
            ->post(route('content-approvals.reject', $version->id), [
                'reason' => 'Needs more work'
            ]);

        $version->refresh();
        $this->assertEquals('rejected', $version->status);
        $this->assertEquals('Needs more work', $version->rejection_reason);
    }

    /** @test */
    public function widget_displays_correct_data_for_editor_role()
    {
        $content = Content::factory()->create();
        $version = ContentVersion::factory()->create([
            'content_id' => $content->id,
            'status' => 'pending',
            'current_step' => 1,
            'assigned_to_role' => 'editor'
        ]);

        $response = $this->actingAs($this->editor)
            ->get(route('dashboard'));

        $response->assertSee('Pending Approvals');
        $response->assertSee($content->title);
        $response->assertSee('Editor Review');
    }

    /** @test */
    public function widget_displays_correct_data_for_admin_role()
    {
        $content = Content::factory()->create();
        $version = ContentVersion::factory()->create([
            'content_id' => $content->id,
            'status' => 'pending',
            'current_step' => 2,
            'assigned_to_role' => 'admin'
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'));

        $response->assertSee('Pending Approvals');
        $response->assertSee($content->title);
        $response->assertSee('Admin Approval');
    }

    /** @test */
    public function unauthorized_users_cannot_access_approval_actions()
    {
        $content = Content::factory()->create();
        $version = ContentVersion::factory()->create([
            'content_id' => $content->id,
            'status' => 'pending',
            'current_step' => 1,
            'assigned_to_role' => 'editor'
        ]);

        $unauthorizedUser = User::factory()->create(['role' => 'viewer']);

        // Attempt to approve
        $response = $this->actingAs($unauthorizedUser)
            ->post(route('content-approvals.approve', $version->id));

        $response->assertForbidden();

        // Attempt to reject
        $response = $this->actingAs($unauthorizedUser)
            ->post(route('content-approvals.reject', $version->id));

        $response->assertForbidden();
    }

    /** @test */
    public function notifications_are_sent_for_all_approval_actions()
    {
        Notification::fake();

        $content = Content::factory()->create();
        $version = ContentVersion::factory()->create([
            'content_id' => $content->id,
            'status' => 'pending',
            'current_step' => 1,
            'assigned_to_role' => 'editor'
        ]);

        // Test approval notification
        $this->actingAs($this->editor)
            ->post(route('content-approvals.approve', $version->id));

        Notification::assertSentTo(
            $this->admin,
            ContentApprovalNotification::class
        );

        // Test rejection notification
        $this->actingAs($this->editor)
            ->post(route('content-approvals.reject', $version->id), [
                'reason' => 'Test rejection'
            ]);

        Notification::assertSentTo(
            $content->creator,
            ContentApprovalNotification::class
        );
    }

    /** @test */
    public function it_integrates_with_content_approval_system()
    {
        $content = Content::factory()->create();
        $version = ContentVersion::factory()->create([
            'content_id' => $content->id,
            'status' => 'pending'
        ]);

        // Verify initial state
        $this->assertEquals('pending', $content->fresh()->status);
        
        // Complete approval workflow
        $this->actingAs($this->editor)
            ->post(route('content-approvals.approve', $version->id));

        $this->actingAs($this->admin)
            ->post(route('content-approvals.approve', $version->id));

        // Verify content status updated
        $this->assertEquals('published', $content->fresh()->status);
    }
}
