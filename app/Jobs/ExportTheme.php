<?php

namespace App\Jobs;

use App\Models\ThemeVersion;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class ExportTheme implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $themeVersion;
    public $user;
    public $format;
    public $includeAssets;
    public $includeMetadata;

    public function __construct(
        ThemeVersion $themeVersion, 
        User $user,
        string $format = 'zip',
        bool $includeAssets = true,
        bool $includeMetadata = true
    ) {
        $this->themeVersion = $themeVersion;
        $this->user = $user;
        $this->format = $format;
        $this->includeAssets = $includeAssets;
        $this->includeMetadata = $includeMetadata;
    }

    public function handle()
    {
        $theme = $this->themeVersion->theme;
        $version = $this->themeVersion;
        
        $exportDir = storage_path("app/exports/themes/{$theme->slug}");
        $exportFilename = "{$theme->slug}-v{$version->version}-export";
        $exportPath = "{$exportDir}/{$exportFilename}";

        // Ensure export directory exists
        if (!File::exists($exportDir)) {
            File::makeDirectory($exportDir, 0755, true);
        }

        if ($this->format === 'zip') {
            $this->exportAsZip($exportPath);
        } else {
            $this->exportAsJson($exportPath);
        }

        // Update version stats
        $version->update([
            'last_exported_at' => now(),
            'export_count' => $version->export_count + 1,
            'export_size' => File::size("{$exportPath}.{$this->format}")
        ]);

        // Notify user
        $this->user->notify(new \App\Notifications\ThemeExportReady(
            $theme,
            $version,
            "{$exportFilename}.{$this->format}"
        ));
    }

    protected function exportAsZip(string $exportPath)
    {
        $zip = new ZipArchive();
        $zipPath = "{$exportPath}.zip";

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            // Add theme files if requested
            if ($this->includeAssets) {
                $themeDir = resource_path("themes/{$this->themeVersion->theme->slug}/v{$this->themeVersion->id}");
                
                if (File::exists($themeDir)) {
                    $files = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($themeDir),
                        \RecursiveIteratorIterator::LEAVES_ONLY
                    );

                    foreach ($files as $file) {
                        if (!$file->isDir()) {
                            $filePath = $file->getRealPath();
                            $relativePath = substr($filePath, strlen($themeDir) + 1);
                            $zip->addFile($filePath, $relativePath);
                        }
                    }
                }
            }

            // Add metadata if requested
            if ($this->includeMetadata) {
                $metadata = [
                    'theme' => $this->themeVersion->theme->toArray(),
                    'version' => $this->themeVersion->toArray(),
                    'exported_at' => now()->toDateTimeString(),
                    'exported_by' => $this->user->toArray()
                ];

                $zip->addFromString('metadata.json', json_encode($metadata, JSON_PRETTY_PRINT));
            }

            $zip->close();
        }
    }

    protected function exportAsJson(string $exportPath)
    {
        $metadata = [
            'theme' => $this->themeVersion->theme->toArray(),
            'version' => $this->themeVersion->toArray(),
            'exported_at' => now()->toDateTimeString(),
            'exported_by' => $this->user->toArray()
        ];

        if ($this->includeAssets) {
            $themeDir = resource_path("themes/{$this->themeVersion->theme->slug}/v{$this->themeVersion->id}");
            
            if (File::exists($themeDir)) {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($themeDir),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                );

                $metadata['files'] = [];
                foreach ($files as $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($themeDir) + 1);
                        $metadata['files'][$relativePath] = [
                            'size' => $file->getSize(),
                            'content' => base64_encode(file_get_contents($filePath))
                        ];
                    }
                }
            }
        }

        file_put_contents("{$exportPath}.json", json_encode($metadata, JSON_PRETTY_PRINT));
    }
}
