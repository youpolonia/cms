<?php

namespace App\Services;

use App\Models\Theme;
use App\Models\ThemeVersion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ThemeService
{
    protected $configCache = [];

    public function getCurrentTheme(): string
    {
        return config('cms.themes.active', 'default');
    }

    public function setTheme(string $theme): bool
    {
        if (!$this->themeExists($theme)) {
            return false;
        }

        // Persist theme setting
        config(['cms.themes.active' => $theme]);
        Cache::forever('active_theme', $theme);

        return true;
    }

    public function getThemePath(string $theme = null): string
    {
        $theme = $theme ?: $this->getCurrentTheme();
        return resource_path("themes/{$theme}");
    }

    public function asset(string $path, string $theme = null): string
    {
        $theme = $theme ?: $this->getCurrentTheme();
        return asset("themes/{$theme}/{$path}");
    }

    public function themeExists(string $theme): bool
    {
        return File::isDirectory($this->getThemePath($theme));
    }

    public function getThemeConfig(string $theme = null): array
    {
        $theme = $theme ?: $this->getCurrentTheme();

        if (isset($this->configCache[$theme])) {
            return $this->configCache[$theme];
        }

        $configPath = $this->getThemePath($theme).'/theme.json';

        if (!File::exists($configPath)) {
            return [];
        }

        try {
            $config = json_decode(File::get($configPath), true);
            $this->configCache[$theme] = $config;
            return $config;
        } catch (\Exception $e) {
            Log::error("Failed to load theme config for {$theme}: ".$e->getMessage());
            return [];
        }
    }

    public function getAvailableThemes(): array
    {
        $themesPath = config('cms.themes.path');
        $directories = File::directories($themesPath);

        return collect($directories)
            ->mapWithKeys(function ($dir) {
                $themeName = basename($dir);
                return [$themeName => $this->getThemeConfig($themeName)['name'] ?? $themeName];
            })
            ->toArray();
    }

    public function createVersion(Theme $theme, array $data, string $description): ThemeVersion
    {
        $version = new ThemeVersion([
            'theme_id' => $theme->id,
            'version_data' => $data,
            'description' => $description,
            'created_at' => now()
        ]);

        $version->save();
        
        // Add to theme's version history
        $theme->version_history = array_merge(
            $theme->version_history ?? [],
            [[
                'version' => $version->id,
                'description' => $description,
                'created_at' => now()->toDateTimeString()
            ]]
        );
        
        $theme->save();

        return $version;
    }
}
