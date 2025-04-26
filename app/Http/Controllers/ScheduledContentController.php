<?php

namespace App\Http\Controllers;

use App\Models\ScheduledContent;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduledContentController extends Controller
{
    public function index()
    {
        return ScheduledContent::with(['content', 'scheduledBy'])
            ->orderBy('publish_at', 'asc')
            ->paginate(15);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'content_id' => 'required|exists:contents,id',
            'publish_at' => 'required|date|after:now',
            'depublish_at' => 'nullable|date|after:publish_at',
            'notes' => 'nullable|string|max:500'
        ]);

        $scheduled = ScheduledContent::create([
            ...$validated,
            'scheduled_by' => Auth::id(),
            'status' => 'pending'
        ]);

        return response()->json($scheduled->load('content'), 201);
    }

    public function show(ScheduledContent $scheduledContent)
    {
        return $scheduledContent->load(['content', 'scheduledBy']);
    }

    public function update(Request $request, ScheduledContent $scheduledContent)
    {
        $validated = $request->validate([
            'publish_at' => 'sometimes|date|after:now',
            'depublish_at' => 'nullable|date|after:publish_at',
            'notes' => 'nullable|string|max:500'
        ]);

        $scheduledContent->update($validated);

        return $scheduledContent->fresh()->load(['content', 'scheduledBy']);
    }

    public function destroy(ScheduledContent $scheduledContent)
    {
        $scheduledContent->delete();
        return response()->noContent();
    }

    public function cancel(ScheduledContent $scheduledContent)
    {
        if ($scheduledContent->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending schedules can be cancelled'
            ], 400);
        }

        $scheduledContent->update(['status' => 'cancelled']);
        return response()->json($scheduledContent->fresh()->load(['content', 'scheduledBy']));
    }
}