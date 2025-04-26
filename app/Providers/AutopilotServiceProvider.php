<?php

namespace App\Providers;

use App\Services\AutopilotService;
use App\Services\TaskExecutionService;
use App\Services\TaskQueueService;
use Illuminate\Support\ServiceProvider;

class AutopilotServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(TaskExecutionService::class, function ($app) {
            return new TaskExecutionService();
        });

        $this->app->singleton(TaskQueueService::class, function ($app) {
            return new TaskQueueService(
                $app->make(TaskExecutionService::class)
            );
        });

        $this->app->singleton(AutopilotService::class, function ($app) {
            return new AutopilotService(
                $app->make(TaskExecutionService::class),
                $app->make(TaskQueueService::class)
            );
        });

        // Register MCP Services
        $this->app->singleton(\App\Services\MCPContentGenerationService::class);
        $this->app->singleton(\App\Services\MCPMediaService::class);
        $this->app->singleton(\App\Services\MCPSearchService::class);
        $this->app->singleton(\App\Services\MCPPersonalizationService::class);
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\AutopilotCommand::class,
            ]);
        }
    }
}