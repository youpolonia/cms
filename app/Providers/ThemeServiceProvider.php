<?php

namespace App\Providers;

use App\Services\ThemeService;
use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ThemeService::class, function ($app) {
            return new ThemeService();
        });

        $this->app->alias(ThemeService::class, 'theme');
    }

    public function boot()
    {
        // Validate theme configuration
        $theme = app('theme')->getCurrentTheme();
        $config = app('theme')->getThemeConfig($theme);

        if (empty($config)) {
            throw new \RuntimeException("Theme configuration missing for {$theme}");
        }

        $required = config('cms.themes.required_fields', []);
        foreach ($required as $field) {
            if (!isset($config[$field])) {
                throw new \RuntimeException("Theme configuration missing required field: {$field}");
            }
        }
    }
}
