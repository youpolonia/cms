<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use OpenAI\Client;
use App\Contracts\DiffServiceInterface;
use App\Services\SemanticDiffService;
use App\Services\ContentDiffService;
use App\Services\MigrationWatcher;
use App\Repositories\Contracts\ContentVersionRepositoryInterface;
use App\Repositories\Eloquent\ContentVersionRepository;
use App\Repositories\AnalyticsRepositoryInterface;
use App\Repositories\AnalyticsRepository;
use App\Contracts\UserAnalyticsServiceInterface;
use App\Services\UserAnalyticsService;
use App\View\Components\ButtonLink;
use App\View\Components\CheckboxInput;
use App\View\Components\ImageUpload;
use App\View\Components\SelectInput;
use App\View\Components\TextareaInput;
use App\View\Components\Icon;
use App\Models\Content;
use App\Observers\ContentObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            DiffServiceInterface::class,
            SemanticDiffService::class
        );
        
        $this->app->bind(ContentDiffService::class, function() {
            return new ContentDiffService();
        });
        
        $this->app->singleton(MigrationWatcher::class, function ($app) {
            return new MigrationWatcher($app->make(DiffServiceInterface::class));
        });

        $this->app->bind(
            ContentVersionRepositoryInterface::class,
            ContentVersionRepository::class
        );

        $this->app->bind(
            UserAnalyticsServiceInterface::class,
            UserAnalyticsService::class
        );

        $this->app->bind(
            AnalyticsRepositoryInterface::class,
            AnalyticsRepository::class
        );

        $this->app->singleton(Client::class, function ($app) {
            return \OpenAI::client(config('openai.api_key'));
        });
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'components');
        
        \Blade::component('button.link', ButtonLink::class);
        \Blade::component('checkbox', CheckboxInput::class);
        \Blade::component('image-upload', ImageUpload::class);
        \Blade::component('select', SelectInput::class);
        \Blade::component('textarea', TextareaInput::class);
        \Blade::component('icon', Icon::class);
        
        Content::observe(ContentObserver::class);
    }
}
