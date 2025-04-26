<?php

namespace App\Providers;

use App\Services\NotificationSoundService;
use Illuminate\Support\ServiceProvider;

class NotificationSoundServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('notification.sounds', function ($app) {
            return new NotificationSoundService();
        });
    }

    public function boot()
    {
        //
    }
}