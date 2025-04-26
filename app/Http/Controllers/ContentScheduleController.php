<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\ContentSchedule;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContentScheduleController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Content/ScheduleList', [
            'schedules' => ContentSchedule::with('content')
                ->orderBy('publish_at')
                ->paginate(15)
        ]);
    }

    public function contentSchedules(Content $content)
    {
        return Inertia::render('Admin/Content/ScheduleList', [
            'schedules' => $content->schedules()
                ->orderBy('publish_at')
                ->paginate(15)
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'content_id' => 'required|exists:contents,id',
            'publish_at' => 'required|date|after:now',
            'unpublish_at' => 'nullable|date|after:publish_at',
            'notes' => 'nullable|string|max:500'
        ]);

        $schedule = ContentSchedule::create($validated);

        return redirect()->back()
            ->with('success', 'Schedule created successfully');
    }

    public function update(Request $request, ContentSchedule $schedule)
    {
        $validated = $request->validate([
            'publish_at' => 'required|date|after:now',
            'unpublish_at' => 'nullable|date|after:publish_at',
            'notes' => 'nullable|string|max:500'
        ]);

        $schedule->update($validated);

        return redirect()->back()
            ->with('success', 'Schedule updated successfully');
    }

    public function destroy(ContentSchedule $schedule)
    {
        $schedule->delete();

        return redirect()->back()
            ->with('success', 'Schedule deleted successfully');
    }
}
