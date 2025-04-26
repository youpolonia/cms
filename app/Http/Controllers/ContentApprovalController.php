<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContentApprovalController extends Controller
{
    public function submitForApproval(Request $request, Content $content)
    {
        $this->authorize('submitForApproval', $content);

        $content->update([
            'approval_status' => 'pending_review',
        ]);

        return response()->json([
            'message' => 'Content submitted for approval',
            'content' => $content->fresh()
        ]);
    }

    public function approve(Request $request, Content $content)
    {
        $this->authorize('approve', $content);

        $content->update([
            'approval_status' => 'approved',
            'reviewer_id' => Auth::id(),
            'reviewed_at' => now(),
            'review_notes' => $request->input('notes', null)
        ]);

        return response()->json([
            'message' => 'Content approved',
            'content' => $content->fresh()
        ]);
    }

    public function reject(Request $request, Content $content)
    {
        $this->authorize('approve', $content);

        $content->update([
            'approval_status' => 'rejected',
            'reviewer_id' => Auth::id(),
            'reviewed_at' => now(),
            'review_notes' => $request->input('notes')
        ]);

        return response()->json([
            'message' => 'Content rejected',
            'content' => $content->fresh()
        ]);
    }

    public function requestChanges(Request $request, Content $content)
    {
        $this->authorize('approve', $content);

        $content->update([
            'approval_status' => 'changes_requested',
            'reviewer_id' => Auth::id(),
            'reviewed_at' => now(),
            'review_notes' => $request->input('notes')
        ]);

        return response()->json([
            'message' => 'Changes requested for content',
            'content' => $content->fresh()
        ]);
    }
}