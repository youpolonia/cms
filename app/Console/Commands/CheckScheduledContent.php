<?php

namespace App\Console\Commands;

use App\Services\ContentSchedulingNotificationService;
use Illuminate\Console\Command;

class CheckScheduledContent extends Command
{
    protected $signature = 'content:check-scheduled';
    protected $description = 'Check for upcoming scheduled content and send notifications';

    public function handle(ContentSchedulingNotificationService $service)
    {
        $this->info('Checking for upcoming scheduled content...');
        $service->notifyUpcomingScheduledContent();
        
        $this->info('Checking for content ready to publish...');
        $service->notifyPublishedContent();
        
        $this->info('Completed scheduled content checks');
    }
}