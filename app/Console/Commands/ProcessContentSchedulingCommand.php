<?php

namespace App\Console\Commands;

use App\Jobs\ContentSchedulingJob;
use Illuminate\Console\Command;

class ProcessContentSchedulingCommand extends Command
{
    protected $signature = 'content:process-scheduling';
    protected $description = 'Process scheduled content publishing and expiration';

    public function handle()
    {
        $this->info('Processing content scheduling...');
        
        $result = ContentSchedulingJob::dispatchSync();
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Published Content', $result['published'] ?? 0],
                ['Expired Content', $result['expired'] ?? 0],
                ['Errors', $result['errors'] ?? 0]
            ]
        );
        
        $this->info('Content scheduling processed successfully');
    }
}