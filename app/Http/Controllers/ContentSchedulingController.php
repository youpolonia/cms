<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Jobs\ContentSchedulingJob;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ContentSchedulingController extends Controller
{
    public function index(Request $request)
    {
        $query = Content::query()
            ->with(['currentVersion', 'scheduledVersion'])
            ->whereHas('versions', function($q) {
                $q->whereNotNull('publish_at')
                    ->where('publish_at', '>', now());
            });

        if ($request->has('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        $scheduledContent = $query->paginate(15);

        return view('content.scheduling.index', [
            'scheduledContent' => $scheduledContent
        ]);
    }

    public function create(Content $content)
    {
        return view('content.scheduling.create', [
            'content' => $content,
            'versions' => $content->versions()
                ->where('is_autosave', false)
                ->orderBy('created_at', 'desc')
                ->get()
        ]);
    }

    public function store(Request $request, Content $content)
    {
        $request->validate([
            'version_id' => 'required|exists:content_versions,id',
            'publish_at' => 'required|date|after:now',
            'unpublish_at' => 'nullable|date|after:publish_at',
            'is_recurring' => 'sometimes|boolean',
            'recurring_frequency' => 'required_if:is_recurring,true|in:daily,weekly,monthly,yearly',
            'recurring_time' => 'required_if:is_recurring,true|date_format:H:i',
            'recurring_end_date' => 'nullable|date|after:publish_at'
        ]);

        $version = ContentVersion::find($request->version_id);

        $version->update([
            'publish_at' => Carbon::parse($request->publish_at),
            'unpublish_at' => $request->unpublish_at
                ? Carbon::parse($request->unpublish_at)
                : null,
            'is_recurring' => $request->is_recurring ?? false
        ]);

        if ($request->is_recurring) {
            $content->recurringSchedules()->create([
                'frequency' => $request->recurring_frequency,
                'time' => $request->recurring_time,
                'start_date' => $request->publish_at,
                'end_date' => $request->recurring_end_date,
                'is_active' => true
            ]);
        }

        ContentSchedulingJob::dispatch($version)
            ->delay($version->publish_at);

        if ($version->unpublish_at) {
            ContentSchedulingJob::dispatch($version, true)
                ->delay($version->unpublish_at);
        }

        return redirect()
            ->route('content.scheduling.index')
            ->with('success', 'Content scheduled successfully');
    }

    public function edit(ContentVersion $version)
    {
        return view('content.scheduling.edit', [
            'version' => $version,
            'content' => $version->content
        ]);
    }

    public function update(Request $request, ContentVersion $version)
    {
        $request->validate([
            'publish_at' => 'required|date|after:now',
            'unpublish_at' => 'nullable|date|after:publish_at'
        ]);

        $version->update([
            'publish_at' => Carbon::parse($request->publish_at),
            'unpublish_at' => $request->unpublish_at 
                ? Carbon::parse($request->unpublish_at)
                : null
        ]);

        // Cancel existing jobs
        $this->cancelScheduledJobs($version);

        // Dispatch new jobs
        ContentSchedulingJob::dispatch($version)
            ->delay($version->publish_at);

        if ($version->unpublish_at) {
            ContentSchedulingJob::dispatch($version, true)
                ->delay($version->unpublish_at);
        }

        return redirect()
            ->route('content.scheduling.index')
            ->with('success', 'Schedule updated successfully');
    }

    public function destroy(ContentVersion $version)
    {
        $this->cancelScheduledJobs($version);

        $version->update([
            'publish_at' => null,
            'unpublish_at' => null
        ]);

        return redirect()
            ->route('content.scheduling.index')
            ->with('success', 'Schedule cancelled successfully');
    }

    public function publishNow(ContentVersion $version)
    {
        $this->cancelScheduledJobs($version);

        $version->update([
            'publish_at' => null,
            'unpublish_at' => null
        ]);

        ContentSchedulingJob::dispatchSync($version);

        return redirect()
            ->route('content.scheduling.index')
            ->with('success', 'Content published immediately');
    }

    protected function cancelScheduledJobs(ContentVersion $version)
    {
        // Cancel any pending scheduling jobs for this version
        $jobs = \DB::table('jobs')
            ->where('payload', 'like', '%"displayName":"App\\\\Jobs\\\\ContentSchedulingJob"%')
            ->where('payload', 'like', '%"version_id":'.$version->id.'%')
            ->get();

        foreach ($jobs as $job) {
            \DB::table('jobs')
                ->where('id', $job->id)
                ->delete();
        }
    }

    public function calendar()
    {
        $events = ContentVersion::query()
            ->whereNotNull('publish_at')
            ->with(['content', 'content.recurringSchedules'])
            ->get()
            ->flatMap(function($version) {
                $events = [];
                
                // Add the main scheduled event
                $events[] = [
                    'title' => $version->content->title . ' (v' . $version->version_number . ')',
                    'start' => $version->publish_at->toIso8601String(),
                    'end' => $version->unpublish_at?->toIso8601String(),
                    'url' => route('content.scheduling.edit', $version),
                    'is_published' => $version->is_published,
                    'is_recurring' => $version->is_recurring,
                    'frequency' => $version->is_recurring
                        ? $version->content->recurringSchedules->first()->frequency
                        : null
                ];

                // If recurring, add future instances
                if ($version->is_recurring) {
                    $schedule = $version->content->recurringSchedules->first();
                    $start = Carbon::parse($version->publish_at);
                    $end = $schedule->end_date ? Carbon::parse($schedule->end_date) : null;
                    
                    $current = $start->copy();
                    while (!$end || $current->lte($end)) {
                        if ($current->gt($start)) { // Skip first instance already added
                            $events[] = [
                                'title' => $version->content->title . ' (v' . $version->version_number . ')',
                                'start' => $current->toIso8601String(),
                                'url' => route('content.scheduling.edit', $version),
                                'is_published' => false,
                                'is_recurring' => true,
                                'frequency' => $schedule->frequency
                            ];
                        }
                        
                        switch ($schedule->frequency) {
                            case 'daily': $current->addDay(); break;
                            case 'weekly': $current->addWeek(); break;
                            case 'monthly': $current->addMonth(); break;
                            case 'yearly': $current->addYear(); break;
                        }
                    }
                }

                return $events;
            });

        return view('content.scheduling.calendar', [
            'events' => $events
        ]);
    }

    public function bulkSchedule(Request $request)
    {
        $request->validate([
            'content_ids' => 'required|array',
            'content_ids.*' => 'exists:contents,id',
            'publish_at' => 'required|date|after:now',
            'unpublish_at' => 'nullable|date|after:publish_at'
        ]);

        $count = 0;

        foreach ($request->content_ids as $contentId) {
            $content = Content::find($contentId);
            $version = $content->currentVersion;

            $version->update([
                'publish_at' => Carbon::parse($request->publish_at),
                'unpublish_at' => $request->unpublish_at 
                    ? Carbon::parse($request->unpublish_at)
                    : null
            ]);

            ContentSchedulingJob::dispatch($version)
                ->delay($version->publish_at);

            if ($version->unpublish_at) {
                ContentSchedulingJob::dispatch($version, true)
                    ->delay($version->unpublish_at);
            }

            $count++;
        }

        return response()->json([
            'success' => true,
            'message' => "Scheduled {$count} content items"
        ]);
    }
}