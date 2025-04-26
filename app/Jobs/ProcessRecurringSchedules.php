<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\RecurringSchedule;
use App\Models\ContentVersion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessRecurringSchedules implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $now = now();
        $schedules = RecurringSchedule::where('is_active', true)
            ->where('next_run_at', '<=', $now)
            ->where(function($query) use ($now) {
                $query->whereNull('end_recurrence_at')
                    ->orWhere('end_recurrence_at', '>', $now);
            })
            ->with('contentVersion')
            ->get();

        foreach ($schedules as $schedule) {
            try {
                $this->processSchedule($schedule);
                $this->updateNextRunTime($schedule);
            } catch (\Exception $e) {
                Log::error("Failed to process schedule {$schedule->id}: " . $e->getMessage());
            }
        }
    }

    protected function processSchedule(RecurringSchedule $schedule)
    {
        // Get the content version to be published
        $version = $schedule->contentVersion;

        // Update the content with the scheduled version
        $version->content->update([
            'current_version_id' => $version->id,
            'published_at' => now()
        ]);

        // Log the scheduled update
        Log::info("Published content version {$version->id} via schedule {$schedule->id}");
    }

    protected function updateNextRunTime(RecurringSchedule $schedule)
    {
        $nextRun = match ($schedule->recurrence_pattern) {
            'daily' => Carbon::parse($schedule->next_run_at)->addDay(),
            'weekly' => $this->calculateNextWeeklyRun($schedule),
            'monthly' => $this->calculateNextMonthlyRun($schedule),
        };

        // Ensure we don't exceed end_recurrence_at if set
        if ($schedule->end_recurrence_at && $nextRun > $schedule->end_recurrence_at) {
            $schedule->update(['is_active' => false]);
            return;
        }

        $schedule->update(['next_run_at' => $nextRun]);
    }

    protected function calculateNextWeeklyRun(RecurringSchedule $schedule)
    {
        $nextRun = Carbon::parse($schedule->next_run_at);
        $daysOfWeek = $schedule->days_of_week;

        // Find the next day in the schedule
        for ($i = 1; $i <= 7; $i++) {
            $nextRun->addDay();
            if (in_array(strtolower($nextRun->format('l')), $daysOfWeek)) {
                return $nextRun->setTimeFromTimeString($schedule->start_time);
            }
        }

        return $nextRun->setTimeFromTimeString($schedule->start_time);
    }

    protected function calculateNextMonthlyRun(RecurringSchedule $schedule)
    {
        $nextRun = Carbon::parse($schedule->next_run_at);
        $daysOfMonth = $schedule->days_of_month;

        // Find the next day in the schedule
        $currentMonth = $nextRun->month;
        $currentYear = $nextRun->year;
        
        // Try next month
        $nextRun->addMonthNoOverflow();
        
        // Find first matching day in next month
        foreach ($daysOfMonth as $day) {
            if ($day <= $nextRun->daysInMonth) {
                return $nextRun->setDate($nextRun->year, $nextRun->month, $day)
                    ->setTimeFromTimeString($schedule->start_time);
            }
        }

        // If no valid day found, use last day of month
        return $nextRun->setDate($nextRun->year, $nextRun->month, $nextRun->daysInMonth)
            ->setTimeFromTimeString($schedule->start_time);
    }
}
