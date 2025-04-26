<?php

namespace App\Http\Controllers;

use App\Models\RecurringSchedule;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class RecurringScheduleController extends Controller
{
    /**
     * Display a listing of recurring schedules for content.
     */
    public function index(Content $content)
    {
        Gate::authorize('view', $content);
        
        return response()->json([
            'data' => $content->recurringSchedules()->get()
        ]);
    }

    /**
     * Store a newly created recurring schedule.
     */
    public function store(Request $request, Content $content)
    {
        Gate::authorize('update', $content);
        
        $validated = $request->validate([
            'frequency' => ['required', Rule::in(['daily', 'weekly', 'monthly', 'yearly'])],
            'time' => ['required', 'date_format:H:i'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'is_active' => ['boolean']
        ]);

        $schedule = $content->recurringSchedules()->create($validated);

        return response()->json([
            'message' => 'Recurring schedule created successfully',
            'data' => $schedule
        ], 201);
    }

    /**
     * Display the specified recurring schedule.
     */
    public function show(RecurringSchedule $recurringSchedule)
    {
        Gate::authorize('view', $recurringSchedule->content);
        
        return response()->json([
            'data' => $recurringSchedule
        ]);
    }

    /**
     * Update the specified recurring schedule.
     */
    public function update(Request $request, RecurringSchedule $recurringSchedule)
    {
        Gate::authorize('update', $recurringSchedule->content);
        
        $validated = $request->validate([
            'frequency' => ['sometimes', Rule::in(['daily', 'weekly', 'monthly', 'yearly'])],
            'time' => ['sometimes', 'date_format:H:i'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'is_active' => ['sometimes', 'boolean']
        ]);

        $recurringSchedule->update($validated);

        return response()->json([
            'message' => 'Recurring schedule updated successfully',
            'data' => $recurringSchedule
        ]);
    }

    /**
     * Remove the specified recurring schedule.
     */
    public function destroy(RecurringSchedule $recurringSchedule)
    {
        Gate::authorize('update', $recurringSchedule->content);
        
        $recurringSchedule->delete();

        return response()->json([
            'message' => 'Recurring schedule deleted successfully'
        ]);
    }
}
