<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\Middleware\TrackContentViews;
use Illuminate\Contracts\Http\Kernel;
use App\Services\ContentTrackingService;

class TrackingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ContentTrackingService::class, function ($app) {
            return new ContentTrackingService();
        });
    }

    public function boot(Kernel $kernel)
    {
        $kernel->appendMiddlewareToGroup('web', TrackContentViews::class);
    }
}
