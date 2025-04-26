<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use App\Models\ThemeVersion;
use App\Notifications\ThemeUpdateAvailable;
use App\Services\ThemeUpdateChecker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ThemeUpdateController extends Controller
{
    public function __construct(
        protected ThemeUpdateChecker $updateChecker
    ) {}

    public function checkUpdates()
    {
        $themes = Theme::with('currentVersion')->get();
        $updates = [];

        foreach ($themes as $theme) {
            if ($update = $this->updateChecker->checkForUpdates($theme)) {
                $updates[] = $update;
                
                // Notify theme owner
                Notification::send(
                    $theme->user,
                    new ThemeUpdateAvailable(
                        $theme,
                        $theme->currentVersion->version,
                        $update['version'],
                        $update['changelog']
                    )
                );
            }
        }

        return response()->json([
            'updates' => $updates,
            'message' => count($updates) ? 'Updates available' : 'All themes are up to date'
        ]);
    }

    public function index()
    {
        $themes = Theme::with(['currentVersion', 'availableUpdates'])
            ->whereHas('availableUpdates')
            ->get();

        return view('themes.updates', compact('themes'));
    }

    public function install(Theme $theme, string $version)
    {
        $this->authorize('update', $theme);

        $update = $theme->availableUpdates()
            ->where('version', $version)
            ->firstOrFail();

        InstallThemeUpdate::dispatch($theme, $update)
            ->onQueue('theme-updates')
            ->delay(now()->addSeconds(5));

        return redirect()->route('themes.install.status', [
            'theme' => $theme,
            'version' => $update,
            'status' => 'pending'
        ]);
    }

    public function status(Theme $theme, ThemeVersion $version, Request $request)
    {
        $this->authorize('update', $theme);

        $status = $request->query('status', 'pending');
        $message = match($status) {
            'success' => "Theme {$theme->name} was successfully updated to version {$version->version}",
            'failed' => "Failed to update theme {$theme->name} to version {$version->version}",
            default => "Theme update is in progress..."
        };

        return view('themes.install', [
            'theme' => $theme,
            'version' => $version,
            'status' => $status,
            'message' => $message
        ]);
    }
}
