<?php

namespace App\Providers;

use App\Http\Livewire\ContentVersionComparison;
use App\Http\Livewire\ContentScheduling;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class LivewireComponentsServiceProvider extends ServiceProvider
{
    public function register()
    {
        Livewire::component('content-version-comparison', ContentVersionComparison::class);
        Livewire::component('content-scheduling', ContentScheduling::class);
    }

    public function boot()
    {
        //
    }
}