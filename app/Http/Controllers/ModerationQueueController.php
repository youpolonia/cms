<?php

namespace App\Http\Controllers;

use App\Models\ModerationQueue;
use App\Services\ModerationService;
use Illuminate\Http\Request;

class ModerationQueueController extends Controller
{
    public function __construct(protected ModerationService $moderationService)
    {
        $this->middleware('auth');
        $this->middleware('can:moderate_content');
    }

    public function index()
    {
        $items = $this->moderationService->getPendingItems();
        return view('moderation.index', compact('items'));
    }

    public function show(ModerationQueue $moderation)
    {
        return view('moderation.show', compact('moderation'));
    }

    public function approve(Request $request, ModerationQueue $moderation)
    {
        $this->moderationService->moderateContent(
            $moderation->content_id,
            'approved',
            $request->user()
        );

        return redirect()->route('moderation.index')
            ->with('success', 'Content approved successfully');
    }

    public function reject(Request $request, ModerationQueue $moderation)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $this->moderationService->moderateContent(
            $moderation->content_id,
            'rejected',
            $request->user(),
            $request->reason
        );

        return redirect()->route('moderation.index')
            ->with('success', 'Content rejected successfully');
    }
}