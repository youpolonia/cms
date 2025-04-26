<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ContentVersion;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalStep;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContentApprovalDashboardWidgetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user with different roles (using JSON roles field)
        $this->admin = User::factory()->create(['roles' => ['admin']]);
        $this->editor = User::factory()->create(['roles' => ['editor']]);
        $this->viewer = User::factory()->create(['roles' => ['viewer']]);
        $this->multiRoleUser = User::factory()->create(['roles' => ['editor', 'reviewer']]);
    }

    /** @test */
    public function it_displays_approval_statistics_correctly()
    {
        ContentVersion::factory()->count(3)->create(['status' => 'pending']);
        ContentVersion::factory()->count(2)->create(['status' => 'approved']);
        ContentVersion::factory()->count(1)->create(['status' => 'rejected']);

        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'));

        $response->assertSee('Pending (3)');
        $response->assertSee('Approved (2)');
        $response->assertSee('Rejected (1)');
    }

    /** @test */
    public function it_shows_workflow_progress_correctly()
    {
        $workflow = ApprovalWorkflow::factory()->create();
        ApprovalStep::factory()->count(3)->create(['workflow_id' => $workflow->id]);
        ApprovalStep::factory()->count(2)->create([
            'workflow_id' => $workflow->id,
            'is_completed' => true
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'));

        $response->assertSee('Step 2 of 5');
    }

    /** @test */
    public function it_displays_user_specific_pending_actions()
    {
        ContentVersion::factory()->count(3)->create([
            'status' => 'pending',
            'current_approval_step_id' => ApprovalStep::factory()->create(['role_id' => $this->editor->roles[0]])
        ]);

        $response = $this->actingAs($this->editor)
            ->get(route('dashboard'));

        $response->assertSee('Your Pending Actions');
        $response->assertSee('3');
    }

    /** @test */
    public function it_lists_pending_approvals_with_actions()
    {
        $version = ContentVersion::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'));

        $response->assertSee($version->content->title);
        $response->assertSee('Approve');
        $response->assertSee('Reject');
    }

    /** @test */
    public function it_shows_notification_badge_when_unread_notifications_exist()
    {
        $this->admin->notifications()->create([
            'type' => 'App\Notifications\ContentApprovalNotification',
            'data' => ['message' => 'New approval required'],
            'read_at' => null
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'));

        $response->assertSee('Content Approvals');
        $response->assertSee('inline-flex items-center justify-center w-4 h-4 rounded-full bg-red-500');
    }

    /** @test */
    public function it_respects_role_based_access_control()
    {
        // Viewer should not see action buttons
        $response = $this->actingAs($this->viewer)
            ->get(route('dashboard'));

        $response->assertDontSee('Approve');
        $response->assertDontSee('Reject');

        // Editor should see action buttons
        $response = $this->actingAs($this->editor)
            ->get(route('dashboard'));

        $response->assertSee('Approve');
        $response->assertSee('Reject');
    }

    /** @test */
    public function it_handles_large_datasets_efficiently()
    {
        ContentVersion::factory()->count(150)->create(['status' => 'pending']);
        
        $start = microtime(true);
        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'));
        $duration = microtime(true) - $start;

        $response->assertSuccessful();
        $this->assertLessThan(1.0, $duration, 'Dashboard should load in under 1 second with 150 pending items');
    }

    /** @test */
    public function it_displays_empty_state_when_no_approvals_exist()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'));

        $response->assertSee('No pending approvals');
        $response->assertDontSee('Approve');
        $response->assertDontSee('Reject');
    }

    /** @test */
    public function it_handles_users_with_multiple_roles()
    {
        // Create approvals for both roles
        ContentVersion::factory()->count(2)->create([
            'status' => 'pending',
            'current_approval_step_id' => ApprovalStep::factory()->create(['role_id' => 'editor'])
        ]);
        ContentVersion::factory()->count(3)->create([
            'status' => 'pending',
            'current_approval_step_id' => ApprovalStep::factory()->create(['role_id' => 'reviewer'])
        ]);

        $response = $this->actingAs($this->multiRoleUser)
            ->get(route('dashboard'));

        $response->assertSee('Your Pending Actions');
        $response->assertSee('5'); // Should show combined count
    }

    /** @test */
    public function it_handles_zero_workflow_states_gracefully()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'));

        $response->assertSee('Step 0 of 0');
        $response->assertSee('width: 0%'); // Progress bar should be empty
    }

    /** @test */
    public function it_shows_notification_badge_only_when_unread_notifications_exist()
    {
        // First check without notifications
        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'));
        $response->assertDontSee('inline-flex items-center justify-center w-4 h-4 rounded-full bg-red-500');

        // Then create an unread notification
        $this->admin->notifications()->create([
            'type' => 'App\Notifications\ContentApprovalNotification',
            'data' => ['message' => 'New approval required'],
            'read_at' => null
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'));
        $response->assertSee('inline-flex items-center justify-center w-4 h-4 rounded-full bg-red-500');
    }

    /** @test */
    public function it_handles_zero_counts_for_all_statuses()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'));

        $response->assertSee('Pending (0)');
        $response->assertSee('Approved (0)');
        $response->assertSee('Rejected (0)');
    }

    /** @test */
    public function it_properly_displays_empty_state_when_no_pending_approvals()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'));

        $response->assertSee('No pending approvals');
        $response->assertDontSee('Approve');
        $response->assertDontSee('Reject');
    }

    /** @test */
    public function it_shows_different_ui_for_viewers_vs_editors()
    {
        // Viewer should see counts but no action buttons
        $response = $this->actingAs($this->viewer)
            ->get(route('dashboard'));
        $response->assertSee('Pending');
        $response->assertDontSee('Approve');
        $response->assertDontSee('Reject');

        // Editor should see action buttons
        $response = $this->actingAs($this->editor)
            ->get(route('dashboard'));
        $response->assertSee('Approve');
        $response->assertSee('Reject');
    }

    /** @test */
    public function it_filters_approvals_by_status()
    {
        ContentVersion::factory()->count(3)->create(['status' => 'pending']);
        ContentVersion::factory()->count(2)->create(['status' => 'approved']);

        $response = $this->actingAs($this->admin)
            ->get(route('dashboard', ['filter' => 'approved']));

        $response->assertSee('Approved (2)');
        $response->assertDontSee('Pending (3)');
    }

    /** @test */
    public function it_handles_invalid_workflow_data_gracefully()
    {
        // Create invalid workflow state where completed > total
        $workflow = ApprovalWorkflow::factory()->create();
        ApprovalStep::factory()->count(3)->create([
            'workflow_id' => $workflow->id,
            'is_completed' => true
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'));

        $response->assertSuccessful();
        $response->assertSee('Step 3 of 3');
    }
}
