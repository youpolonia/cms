<?php

namespace App\Providers;

use App\Services\AutoClassificationService;
use Illuminate\Support\ServiceProvider;

class AutoClassificationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(AutoClassificationService::class, function () {
            return new AutoClassificationService();
        });
    }

    public function boot()
    {
        //
    }
}