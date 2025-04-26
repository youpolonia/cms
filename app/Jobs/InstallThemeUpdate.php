<?php

namespace App\Jobs;

use App\Models\Theme;
use App\Models\ThemeVersion;
use App\Notifications\ThemeUpdateInstalled;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class InstallThemeUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Theme $theme,
        public ThemeVersion $version
    ) {}

    public function handle(): bool
    {
        try {
            $service = app(ThemeService::class);
            
            // Validate version can be installed
            if (!$this->version->marketplace_id) {
                throw new \RuntimeException(
                    "Cannot install theme version - missing marketplace ID"
                );
            }

            // Check if already up-to-date
            if ($this->theme->current_version_id === $this->version->id) {
                \Log::info("Theme already at requested version", [
                    'theme_id' => $this->theme->id,
                    'version_id' => $this->version->id
                ]);
                return true;
            }

            // Check dependencies
            $missingDeps = $service->checkDependencies($this->version);
            if (!empty($missingDeps)) {
                throw new \RuntimeException(
                    "Missing dependencies: " . implode(', ', $missingDeps)
                );
            }

            // Download and install the new version
            $service->downloadVersion(
                $this->theme,
                $this->version->marketplace_id
            );

            // Activate the new version if theme is currently active
            if ($this->theme->is_active) {
                $service->activateVersion(
                    $this->theme,
                    $this->version
                );
            }

            // Update theme's current version
            $this->theme->update([
                'current_version_id' => $this->version->id,
                'updated_at' => now()
            ]);

            // Clear any cached theme files
            $service->clearThemeCache($this->theme);

            // Notify users who have opted in for installation notifications
            $this->theme->users()
                ->where('notification_preferences->theme_installed', true)
                ->each(function ($user) {
                    $user->notify(new ThemeUpdateInstalled(
                        $this->theme,
                        $this->version,
                        false
                    ));
                });

            \Log::info("Successfully installed theme update", [
                'theme_id' => $this->theme->id,
                'version_id' => $this->version->id
            ]);

            return true;

        } catch (\Exception $e) {
            \Log::error("Failed to install theme update", [
                'theme_id' => $this->theme->id,
                'version_id' => $this->version->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Notify admin users of failure
            $this->theme->users()
                ->where('notification_preferences->theme_install_failed', true)
                ->each(function ($user) use ($e) {
                    $user->notify(new ThemeUpdateInstalled(
                        $this->theme,
                        $this->version,
                        true,
                        $e->getMessage()
                    ));
                });

            return false;
        }
    }
}
