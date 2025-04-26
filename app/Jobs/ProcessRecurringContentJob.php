<?php

namespace App\Jobs;

use App\Models\Content;
use App\Models\RecurringSchedule;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRecurringContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $today = Carbon::today();

        RecurringSchedule::where('end_date', '>=', $today)
            ->where('start_date', '<=', $today)
            ->chunk(100, function ($schedules) use ($today) {
                foreach ($schedules as $schedule) {
                    $this->processSchedule($schedule, $today);
                }
            });
    }

    protected function processSchedule(RecurringSchedule $schedule, Carbon $today)
    {
        $lastRun = $schedule->last_run_at ? Carbon::parse($schedule->last_run_at) : null;
        $shouldRun = false;

        switch ($schedule->frequency) {
            case 'daily':
                $shouldRun = !$lastRun || $lastRun->lt($today);
                break;
            case 'weekly':
                $shouldRun = !$lastRun || $lastRun->diffInWeeks($today) >= 1;
                break;
            case 'monthly':
                $shouldRun = !$lastRun || $lastRun->diffInMonths($today) >= 1;
                break;
            case 'yearly':
                $shouldRun = !$lastRun || $lastRun->diffInYears($today) >= 1;
                break;
        }

        if ($shouldRun) {
            $this->createContentFromSchedule($schedule);
            $schedule->update(['last_run_at' => $today]);
        }
    }

    protected function createContentFromSchedule(RecurringSchedule $schedule)
    {
        $content = $schedule->content;
        
        Content::create([
            'title' => $content->title,
            'content' => $content->content,
            'content_type' => $content->content_type,
            'slug' => $content->slug . '-' . now()->format('Y-m-d'),
            'user_id' => $content->user_id,
            'seo_title' => $content->seo_title,
            'seo_description' => $content->seo_description,
            'seo_keywords' => $content->seo_keywords,
            'is_recurring' => false
        ]);
    }
}