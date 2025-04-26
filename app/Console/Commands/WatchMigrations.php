<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MigrationWatcher;

class WatchMigrations extends Command
{
    protected $signature = 'migrations:watch';
    protected $description = 'Watch for migration file changes and update documentation';

    public function handle(MigrationWatcher $watcher)
    {
        $this->info('Starting migration file watcher...');
        $watcher->startWatching();
        
        while (true) {
            sleep(1);
        }
    }
}