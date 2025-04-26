<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('content:process-scheduled', function () {
    $this->call(\App\Console\Commands\ProcessScheduledContent::class);
})->purpose('Process scheduled content publishing and expiration');

Schedule::command('content:process-scheduled')->everyMinute();

Artisan::command('watch:migrations', function () {
    $this->call(\App\Console\Commands\WatchMigrations::class);
})->purpose('Watch for database migration changes and update documentation');
