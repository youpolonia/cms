<?php

namespace App\Jobs;

use App\Models\ThemeVersion;
use App\Models\User;
use App\Services\ThemeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Notifications\ThemeExportReady;

class ProcessThemeExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected ThemeVersion $version,
        protected string $format,
        protected User $user
    ) {}

    public function handle(ThemeService $themeService)
    {
        $path = $themeService->exportVersion($this->version, $this->format);
        $this->user->notify(new ThemeExportReady($path, $this->version));
    }
}