<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessRecurringSchedules;

class ProcessSchedules extends Command
{
    protected $signature = 'schedules:process';
    protected $description = 'Process all recurring content schedules that are due';

    public function handle()
    {
        $this->info('Starting schedule processing...');
        
        dispatch(new ProcessRecurringSchedules());

        $this->info('Schedule processing job dispatched successfully.');
        
        return Command::SUCCESS;
    }
}