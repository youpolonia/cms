<?php

namespace Tests\Feature;

use App\Models\Theme;
use App\Models\ThemeVersion;
use App\Models\ThemeVersionApproval;
use App\Models\User;
use App\Notifications\ThemeApprovalNotification;
use App\Services\ApprovalWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ThemeApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected ApprovalWorkflowService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ApprovalWorkflowService::class);
    }

    public function test_send_approval_request_notifications()
    {
        Notification::fake();

        $submitter = User::factory()->create();
        $approver = User::factory()->create();
        $theme = Theme::factory()->create(['user_id' => $submitter->id]);
        $version = ThemeVersion::factory()->create(['theme_id' => $theme->id]);
        $approval = ThemeVersionApproval::factory()->create([
            'theme_version_id' => $version->id,
            'approvers' => [$approver->id]
        ]);

        $this->service->sendApprovalRequest($version);

        Notification::assertSentTo(
            $submitter,
            ThemeApprovalNotification::class,
            fn ($notification) => $notification->type === 'requested'
        );

        Notification::assertSentTo(
            $approver,
            ThemeApprovalNotification::class,
            fn ($notification) => $notification->type === 'approval_required'
        );
    }

    public function test_send_approval_notification()
    {
        Notification::fake();

        $submitter = User::factory()->create();
        $theme = Theme::factory()->create(['user_id' => $submitter->id]);
        $version = ThemeVersion::factory()->create(['theme_id' => $theme->id]);
        $approval = ThemeVersionApproval::factory()->create([
            'theme_version_id' => $version->id,
            'settings' => ['notify_on_completion' => true]
        ]);

        $this->service->sendApprovalNotification($version);

        Notification::assertSentTo(
            $submitter,
            ThemeApprovalNotification::class,
            fn ($notification) => $notification->type === 'approved'
        );
    }

    public function test_send_rejection_notification()
    {
        Notification::fake();

        $submitter = User::factory()->create();
        $rejector = User::factory()->create();
        $theme = Theme::factory()->create(['user_id' => $submitter->id]);
        $version = ThemeVersion::factory()->create(['theme_id' => $theme->id]);
        $approval = ThemeVersionApproval::factory()->create([
            'theme_version_id' => $version->id,
            'settings' => ['notify_on_rejection' => true]
        ]);

        $this->service->sendRejectionNotification($version, $rejector, 'Test rejection reason');

        Notification::assertSentTo(
            $submitter,
            ThemeApprovalNotification::class,
            fn ($notification) => 
                $notification->type === 'rejected' &&
                $notification->approval->rejection_reason === 'Test rejection reason'
        );
    }

    public function test_step_transition_notifications()
    {
        Notification::fake();

        $submitter = User::factory()->create();
        $approvers = User::factory(3)->create();
        $theme = Theme::factory()->create(['user_id' => $submitter->id]);
        $version = ThemeVersion::factory()->create(['theme_id' => $theme->id]);
        $approval = ThemeVersionApproval::factory()
            ->withSteps(3)
            ->create(['theme_version_id' => $version->id]);

        // Test step 1 to 2 transition
        $this->service->advanceToNextStep($version);
        
        Notification::assertSentTo(
            $approvers[1],
            ThemeApprovalNotification::class,
            fn ($notification) => $notification->type === 'approval_required'
        );

        // Test step 2 to 3 transition
        $this->service->advanceToNextStep($version);
        
        Notification::assertSentTo(
            $approvers[2],
            ThemeApprovalNotification::class,
            fn ($notification) => $notification->type === 'approval_required'
        );
    }

    public function test_workflow_completion()
    {
        Notification::fake();

        $submitter = User::factory()->create();
        $theme = Theme::factory()->create(['user_id' => $submitter->id]);
        $version = ThemeVersion::factory()->create(['theme_id' => $theme->id]);
        $approval = ThemeVersionApproval::factory()
            ->withSteps(3)
            ->atStep(3)
            ->create(['theme_version_id' => $version->id]);

        $this->service->completeApproval($version);

        $this->assertEquals(100, $approval->fresh()->progress);
        Notification::assertSentTo(
            $submitter,
            ThemeApprovalNotification::class,
            fn ($notification) => $notification->type === 'approved'
        );
    }

    public function test_step_rejection()
    {
        Notification::fake();

        $submitter = User::factory()->create();
        $rejector = User::factory()->create();
        $theme = Theme::factory()->create(['user_id' => $submitter->id]);
        $version = ThemeVersion::factory()->create(['theme_id' => $theme->id]);
        $approval = ThemeVersionApproval::factory()
            ->withSteps(3)
            ->atStep(2)
            ->create(['theme_version_id' => $version->id]);

        $this->service->rejectAtCurrentStep($version, $rejector, 'Step rejection reason');

        $this->assertEquals(0, $approval->fresh()->progress);
        Notification::assertSentTo(
            $submitter,
            ThemeApprovalNotification::class,
            fn ($notification) => 
                $notification->type === 'rejected' &&
                $notification->approval->rejection_reason === 'Step rejection reason'
        );
    }
}
