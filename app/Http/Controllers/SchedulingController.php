<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\ContentSchedule;
use App\Services\ContentSchedulingService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SchedulingController extends Controller
{
    protected $schedulingService;

    public function __construct(ContentSchedulingService $schedulingService)
    {
        $this->schedulingService = $schedulingService;
    }

    public function schedule(Request $request, Content $content, ContentVersion $version)
    {
        $request->validate([
            'publish_at' => 'required|date',
            'unpublish_at' => 'nullable|date|after:publish_at',
            'is_recurring' => 'boolean',
            'recurrence_rule' => 'nullable|string|required_if:is_recurring,true'
        ]);

        // Check for conflicts
        $conflicts = $this->schedulingService->checkForConflicts(
            $content,
            Carbon::parse($request->publish_at),
            $request->unpublish_at ? Carbon::parse($request->unpublish_at) : null
        );

        if (!empty($conflicts)) {
            return response()->json([
                'message' => 'Scheduling conflicts detected',
                'conflicts' => $conflicts
            ], 409);
        }

        $schedule = $this->schedulingService->scheduleContent(
            $content,
            $version,
            Carbon::parse($request->publish_at),
            $request->unpublish_at ? Carbon::parse($request->unpublish_at) : null,
            $request->is_recurring ?? false,
            $request->recurrence_rule
        );

        return response()->json([
            'message' => 'Content scheduled successfully',
            'schedule' => $schedule
        ]);
    }

    public function cancel(Content $content)
    {
        $this->schedulingService->cancelSchedules($content);

        return response()->json([
            'message' => 'All schedules cancelled for this content'
        ]);
    }

    public function history(Content $content)
    {
        $history = $this->schedulingService->getContentScheduleHistory($content);

        return response()->json($history);
    }

    public function upcoming()
    {
        $upcoming = $this->schedulingService->getUpcomingSchedules();

        return response()->json($upcoming);
    }

    public function checkConflicts(Request $request, Content $content)
    {
        $request->validate([
            'publish_at' => 'required|date',
            'unpublish_at' => 'nullable|date|after:publish_at'
        ]);

        $conflicts = $this->schedulingService->checkForConflicts(
            $content,
            Carbon::parse($request->publish_at),
            $request->unpublish_at ? Carbon::parse($request->unpublish_at) : null
        );

        return response()->json([
            'has_conflicts' => !empty($conflicts),
            'conflicts' => $conflicts
        ]);
    }

    public function reschedule(Request $request, ContentSchedule $schedule)
    {
        $request->validate([
            'new_publish_at' => 'required|date',
            'new_unpublish_at' => 'nullable|date|after:new_publish_at'
        ]);

        $newSchedule = $this->schedulingService->reschedule(
            $schedule,
            Carbon::parse($request->new_publish_at),
            $request->new_unpublish_at ? Carbon::parse($request->new_unpublish_at) : null
        );

        return response()->json([
            'message' => 'Schedule updated successfully',
            'schedule' => $newSchedule
        ]);
    }

    public function checkPeriodConflicts(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date'
        ]);

        $conflicts = $this->schedulingService->getScheduleConflictsForPeriod(
            Carbon::parse($request->start_date),
            Carbon::parse($request->end_date)
        );

        return response()->json([
            'has_conflicts' => !empty($conflicts),
            'conflicts' => $conflicts
        ]);
    }

    public function processOverdue()
    {
        $this->schedulingService->processOverdueSchedules();

        return response()->json([
            'message' => 'Overdue schedules processed'
        ]);
    }
}