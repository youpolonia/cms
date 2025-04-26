<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use OpenAI\Laravel\Facades\OpenAI;
use App\Services\OpenAIContentService;
use App\Contracts\ContentGenerationServiceInterface;

class OpenAIServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(ContentGenerationServiceInterface::class, function ($app) {
            return new OpenAIContentService(
                OpenAI::client(),
                config('openai.default_model'),
                config('openai.generation')
            );
        });
    }

    public function boot()
    {
        //
    }
}