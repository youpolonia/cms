<?php

namespace App\Http\Controllers;

use App\Models\ThemeVersion;
use App\Models\ThemeApprovalWorkflow;
use App\Models\ThemeVersionApproval;
use Illuminate\Http\Request;

class ThemeApprovalController extends Controller
{
    public function initiateApproval(Request $request, ThemeVersion $version)
    {
        $workflow = ThemeApprovalWorkflow::active()->firstOrFail();
        
        $approval = ThemeVersionApproval::create([
            'theme_version_id' => $version->id,
            'workflow_id' => $workflow->id,
            'status' => 'pending',
            'total_steps' => $workflow->steps->count(),
            'current_step_id' => $workflow->steps->first()->id
        ]);

        return response()->json([
            'message' => 'Approval process initiated',
            'approval' => $approval,
            'current_step' => $approval->currentStep
        ]);
    }

    public function approveStep(Request $request, ThemeVersionApproval $approval)
    {
        $this->authorize('approve', $approval);

        $approval->approveCurrentStep($request->user());

        return response()->json([
            'message' => 'Step approved',
            'approval' => $approval->fresh(),
            'next_step' => $approval->currentStep
        ]);
    }

    public function rejectStep(Request $request, ThemeVersionApproval $approval)
    {
        $this->authorize('approve', $approval);

        $approval->update([
            'status' => 'rejected',
            'rejected_by' => $request->user()->id,
            'rejected_at' => now(),
            'notes' => $request->input('notes')
        ]);

        return response()->json([
            'message' => 'Approval rejected',
            'approval' => $approval
        ]);
    }

    public function getApprovalStatus(ThemeVersionApproval $approval)
    {
        return response()->json([
            'status' => $approval->status,
            'progress' => $approval->getProgressStatus(),
            'current_step' => $approval->currentStep,
            'is_complete' => $approval->isComplete()
        ]);
    }
}
