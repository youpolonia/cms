<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CleanupThemeExports implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $files = Storage::files('theme-exports');
        $now = now();
        $deleted = 0;

        foreach ($files as $file) {
            $lastModified = Storage::lastModified($file);
            $fileAge = $now->diffInDays(\Carbon\Carbon::createFromTimestamp($lastModified));

            if ($fileAge > 7) { // Delete exports older than 7 days
                Storage::delete($file);
                $deleted++;
            }
        }

        \Log::info("Cleaned up $deleted old theme exports");
    }
}
