<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalStep;
use App\Models\ApprovalDecision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalWorkflowController extends Controller
{
    public function create(Content $content)
    {
        return view('approvals.create', compact('content'));
    }

    public function store(Request $request, Content $content)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'steps' => 'required|array|min:1',
            'steps.*.name' => 'required|string|max:255',
            'steps.*.approvers' => 'required|array|min:1',
            'steps.*.approvers.*' => 'exists:users,id'
        ]);

        $workflow = ApprovalWorkflow::create([
            'content_id' => $content->id,
            'name' => $validated['name'],
            'created_by' => Auth::id()
        ]);

        foreach ($validated['steps'] as $stepData) {
            $step = $workflow->steps()->create([
                'name' => $stepData['name'],
                'order' => $workflow->steps()->count() + 1
            ]);

            $step->approvers()->sync($stepData['approvers']);
        }

        $content->update(['approval_workflow_id' => $workflow->id]);

        return redirect()->route('contents.show', $content)
            ->with('success', 'Approval workflow created successfully');
    }

    public function decide(ApprovalStep $step, Request $request)
    {
        $request->validate([
            'decision' => 'required|in:approved,rejected',
            'comments' => 'nullable|string'
        ]);

        $decision = $step->decisions()->create([
            'user_id' => Auth::id(),
            'decision' => $request->decision,
            'comments' => $request->comments
        ]);

        // Handle workflow progression based on decision
        $step->workflow->progress($decision);

        return back()->with('success', 'Decision recorded');
    }
}