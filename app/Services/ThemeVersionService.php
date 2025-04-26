<?php

namespace App\Services;

use App\Models\Theme;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ThemeVersionService
{
    protected $checkUrl = 'https://api.cms-themes.com/v1/version-check';

    public function checkForUpdates(Theme $theme)
    {
        try {
            $response = Http::timeout(10)
                ->retry(3, 100)
                ->post($this->checkUrl, [
                    'theme_id' => $theme->id,
                    'current_version' => $theme->current_version
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                $theme->update([
                    'update_available_version' => $data['latest_version'] ?? null,
                    'update_available_url' => $data['download_url'] ?? null,
                    'last_checked_at' => now()
                ]);

                return true;
            }
        } catch (\Exception $e) {
            Log::error("Theme version check failed for theme {$theme->id}: " . $e->getMessage());
        }

        return false;
    }

    public function updateTheme(Theme $theme)
    {
        if (!$theme->hasUpdateAvailable()) {
            return false;
        }

        try {
            // In a real implementation, this would download and install the update
            $theme->addVersionToHistory($theme->current_version, 'Before update');
            $theme->current_version = $theme->update_available_version;
            $theme->update_available_version = null;
            $theme->update_available_url = null;
            $theme->save();

            return true;
        } catch (\Exception $e) {
            Log::error("Theme update failed for theme {$theme->id}: " . $e->getMessage());
            return false;
        }
    }

    public function rollbackTheme(Theme $theme, string $version)
    {
        $history = $theme->version_history;
        $targetVersion = collect($history)->firstWhere('version', $version);

        if (!$targetVersion) {
            return false;
        }

        try {
            // In a real implementation, this would restore the specific version
            $theme->addVersionToHistory($theme->current_version, 'Before rollback');
            $theme->current_version = $version;
            $theme->save();

            return true;
        } catch (\Exception $e) {
            Log::error("Theme rollback failed for theme {$theme->id}: " . $e->getMessage());
            return false;
        }
    }
}