<?php

namespace App\Console;

use App\Jobs\CheckThemeUpdatesJob;
use App\Jobs\ProcessRecurringContentJob;
use App\Jobs\ProcessRecurringSchedules;
use App\Jobs\ProcessScheduledContent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\WatchMigrations;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\ProcessScheduledContent::class,
        \App\Console\Commands\ProcessRecurringSchedulesCommand::class,
        \App\Console\Commands\ProcessSchedules::class,
        WatchMigrations::class,
        \App\Console\Commands\ProcessScheduledExports::class,
        \App\Console\Commands\GenerateApiDocumentation::class,
        \App\Console\Commands\DispatchRoadmapTasks::class,
        \App\Console\Commands\CleanupAutosaveVersions::class,
        \App\Console\Commands\PublishScheduledContent::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Check for theme updates daily at 3:00 AM
        $schedule->job(new CheckThemeUpdatesJob)
            ->dailyAt('3:00')
            ->onOneServer()
            ->onOneServer()
            ->withoutOverlapping();

        // Process content scheduling every minute
        $schedule->command('content:process-scheduled')
            ->everyMinute()
            ->onOneServer()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/scheduled-content.log'));

        // Process scheduled content every minute
        $schedule->command('content:process-scheduled')
            ->everyMinute()
            ->onOneServer()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/scheduled-content.log'));

        // Publish scheduled content every minute
        $schedule->command('content:publish-scheduled')
            ->everyMinute()
            ->onOneServer()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/publish-content.log'));

        // Clean up expired exports daily at 2:00 AM
        $schedule->job(new \App\Jobs\CleanupExpiredExports)
            ->dailyAt('2:00')
            ->onOneServer()
            ->withoutOverlapping();

        // Check for expiring exports hourly
        $schedule->job(new \App\Jobs\CheckExpiringExports)
            ->hourly()
            ->onOneServer()
            ->withoutOverlapping();

        // Process scheduled exports hourly
        $schedule->job(new \App\Jobs\ProcessScheduledExports)
            ->hourly()
            ->onOneServer()
            ->withoutOverlapping();

        // Process recurring content daily at midnight
        $schedule->job(new ProcessRecurringContentJob)
            ->dailyAt('00:01')
            ->onOneServer()
            ->withoutOverlapping();

        // Process recurring schedules every minute
        $schedule->job(new ProcessRecurringSchedules())
            ->everyMinute()
            ->onOneServer()
            ->withoutOverlapping();

        // Process scheduled content every minute
        $schedule->job(new ProcessScheduledContent())
            ->everyMinute()
            ->onOneServer()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/scheduled-content.log'));

        // Process scheduled exports every minute
        $schedule->command('exports:process-scheduled')
            ->everyMinute()
            ->onOneServer()
            ->withoutOverlapping();

        // Process roadmap tasks daily at 4:00 AM
        $schedule->command('roadmap:dispatch')
            ->dailyAt('04:00')
            ->onOneServer()
            ->withoutOverlapping();

        // Clean up old autosave versions daily at 1:00 AM
        $schedule->command('versions:cleanup-autosaves')
            ->dailyAt('01:00')
            ->onOneServer()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/autosave-cleanup.log'));
    }
}
