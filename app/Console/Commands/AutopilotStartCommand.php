<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AutopilotService;

class AutopilotStartCommand extends Command
{
    protected $signature = 'autopilot:start 
                            {--force : Force restart if already running}
                            {--once : Run a single cycle and exit}';
    
    protected $description = 'Start the new autopilot service';

    public function __construct(
        protected AutopilotService $autopilot
    ) {
        parent::__construct();
    }

    public function handle()
    {
        if ($this->autopilot->isActive() && !$this->option('force')) {
            $this->error('Autopilot is already running (use --force to restart)');
            return 1;
        }

        try {
            if ($this->option('once')) {
                $this->info('Running single autopilot cycle');
                $this->autopilot->processTasks();
            } else {
                $this->info('Starting autopilot service');
                $this->autopilot->start();
            }
            return 0;
        } catch (\Exception $e) {
            $this->error('Autopilot failed: ' . $e->getMessage());
            return 1;
        }
    }
}