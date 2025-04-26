<?php

namespace App\Http\Controllers;

use App\Models\ContentVersion;
use App\Models\ContentModeration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContentModerationController extends Controller
{
    public function moderate(Request $request, ContentVersion $version)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,request_changes',
            'notes' => 'nullable|string',
            'changes_requested' => 'nullable|array'
        ]);

        $moderation = ContentModeration::create([
            'content_version_id' => $version->id,
            'moderator_id' => Auth::id(),
            'action' => $request->action,
            'notes' => $request->notes,
            'changes_requested' => $request->changes_requested
        ]);

        // Update version status based on action
        switch($request->action) {
            case 'approve':
                $version->update([
                    'approval_status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => Auth::id()
                ]);
                break;
            case 'reject':
                $version->update([
                    'approval_status' => 'rejected',
                    'rejection_reason' => $request->notes
                ]);
                break;
            case 'request_changes':
                $version->update([
                    'approval_status' => 'changes_requested',
                    'reviewed_at' => now(),
                    'reviewed_by' => Auth::id()
                ]);
                break;
        }

        return redirect()->back()->with('success', 'Moderation action recorded');
    }

    public function history(ContentVersion $version)
    {
        return view('content.moderation-history', [
            'version' => $version,
            'moderations' => $version->moderations()->with('moderator')->latest()->get()
        ]);
    }
}