<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\KnowledgeService;

class KnowledgeServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('knowledge', function ($app) {
            return new KnowledgeService();
        });
    }

    public function boot()
    {
        if (!$this->app->runningInConsole()) {
            $this->app->make('knowledge')->registerRoutes();
        }
    }
}
