<?php

namespace App\Console\Commands;

use App\Services\AutopilotService;
use Illuminate\Console\Command;

class AutopilotCommand extends Command
{
    protected $signature = 'autopilot {action : start|stop|status}';
    protected $description = 'Control the autonomous task execution system';

    public function handle(AutopilotService $autopilot)
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'start':
                if ($autopilot->isActive()) {
                    $this->error('Autopilot is already running');
                    return 1;
                }
                
                $this->info('Starting autopilot...');
                $autopilot->start();
                break;

            case 'stop':
                if (!$autopilot->isActive()) {
                    $this->error('Autopilot is not running');
                    return 1;
                }
                
                $this->info('Stopping autopilot...');
                $autopilot->stop();
                break;

            case 'status':
                $this->info($autopilot->isActive() 
                    ? 'Autopilot is running' 
                    : 'Autopilot is stopped');
                break;

            default:
                $this->error("Invalid action: {$action}");
                $this->line('Available actions: start, stop, status');
                return 1;
        }

        return 0;
    }
}