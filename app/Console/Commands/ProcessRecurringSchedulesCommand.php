<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessRecurringSchedules;

class ProcessRecurringSchedulesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedules:process-recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process all recurring content schedules that are due';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting recurring schedules processing...');
        
        ProcessRecurringSchedules::dispatch()
            ->onQueue('schedules');
            
        $this->info('Recurring schedules processing job dispatched successfully');
    }
}
