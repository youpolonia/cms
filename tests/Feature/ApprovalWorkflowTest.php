<?php

namespace Tests\Feature;

use App\Models\ApprovalStep;
use App\Models\ApprovalWorkflow;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class ApprovalWorkflowTest extends TestCase
{
    public function test_step_order_must_be_unique_per_workflow()
    {
        $workflow = ApprovalWorkflow::factory()->create();

        // Create first step with order 1
        ApprovalStep::factory()->create([
            'workflow_id' => $workflow->id,
            'order' => 1
        ]);

        // Attempt to create another step with same workflow_id and order
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('Duplicate entry');

        ApprovalStep::factory()->create([
            'workflow_id' => $workflow->id,
            'order' => 1
        ]);
    }

    public function test_different_workflows_can_have_same_step_order()
    {
        $workflow1 = ApprovalWorkflow::factory()->create();
        $workflow2 = ApprovalWorkflow::factory()->create();

        // Both workflows can have a step with order 1
        ApprovalStep::factory()->create([
            'workflow_id' => $workflow1->id,
            'order' => 1
        ]);

        ApprovalStep::factory()->create([
            'workflow_id' => $workflow2->id,
            'order' => 1
        ]);

        $this->assertDatabaseCount('approval_steps', 2);
    }
}
