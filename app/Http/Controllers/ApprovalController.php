<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\ApprovalDecision;
use App\Services\WorkflowProcessor;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    protected $workflowProcessor;

    public function __construct(WorkflowProcessor $workflowProcessor)
    {
        $this->workflowProcessor = $workflowProcessor;
    }

    public function submitForApproval(Content $content)
    {
        $this->workflowProcessor->initiateApproval($content);
        return redirect()->back()->with('success', 'Content submitted for approval');
    }

    public function recordDecision(Request $request, Content $content)
    {
        $decision = ApprovalDecision::create([
            'content_id' => $content->id,
            'step_id' => $content->current_approval_step_id,
            'user_id' => auth()->id(),
            'decision' => $request->decision,
            'comments' => $request->comments,
            'changes_requested' => $request->changes_requested
        ]);

        $this->workflowProcessor->processDecision($decision);

        return redirect()->back()->with('success', 'Decision recorded');
    }
}
