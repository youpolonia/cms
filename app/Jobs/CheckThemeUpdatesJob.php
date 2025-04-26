<?php

namespace App\Jobs;

use App\Models\Theme;
use App\Services\ThemeVersionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckThemeUpdatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(ThemeVersionService $versionService)
    {
        $themes = Theme::all();

        foreach ($themes as $theme) {
            try {
                $versionService->checkForUpdates($theme);
            } catch (\Exception $e) {
                report($e);
                continue;
            }
        }
    }
}
