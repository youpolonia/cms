<?php

namespace App\Jobs;

use App\Models\Theme;
use App\Models\ThemeVersion;
use App\Models\ThemeVersionRollback;
use App\Notifications\ThemeRollbackCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class RollbackThemeVersion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $theme;
    public $currentVersion;
    public $rollbackVersion;
    public $rollbackRecord;

    public function __construct(
        Theme $theme,
        ThemeVersion $currentVersion,
        ThemeVersion $rollbackVersion,
        ThemeVersionRollback $rollbackRecord
    ) {
        $this->theme = $theme;
        $this->currentVersion = $currentVersion;
        $this->rollbackVersion = $rollbackVersion;
        $this->rollbackRecord = $rollbackRecord;
        
        $this->rollbackRecord->update([
            'user_id' => auth()->id(),
            'started_at' => now(),
            'branch_name' => $currentVersion->branch_name,
            'rollback_branch' => $rollbackVersion->branch_name
        ]);
        
        $this->theme->notifyAdmins(new \App\Notifications\ThemeRollbackNotification(
            $this->rollbackRecord,
            'initiated'
        ));
    }

    public function handle()
    {
        try {
            $this->rollbackRecord->update(['status' => 'processing']);

            $rollbackFiles = $this->getVersionFiles($this->rollbackVersion);
            $backupPath = $this->createBackup($this->currentVersion);
            $this->restoreFiles($rollbackFiles);

            $this->theme->update([
                'current_version_id' => $this->rollbackVersion->id,
                'current_branch' => $this->rollbackVersion->branch_name
            ]);

            $this->rollbackRecord->update([
                'status' => 'completed',
                'completed_at' => now(),
                'backup_path' => $backupPath,
                'file_changes' => $this->getFileChanges($rollbackFiles)
            ]);

            $this->theme->notifyAdmins(new \App\Notifications\ThemeRollbackCompleted(
                $this->rollbackRecord
            ));

        } catch (\Exception $e) {
            $this->rollbackRecord->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            
            $this->theme->notifyAdmins(new \App\Notifications\ThemeRollbackNotification(
                $this->rollbackRecord,
                'failed'
            ));
            
            $this->rollbackRecord->update([
                'notification_settings' => array_merge(
                    $this->rollbackRecord->notification_settings ?? [],
                    ['notify_on_initiation' => true, 'notify_on_completion' => true]
                )
            ]);
            
            throw $e;
        }
    }

    protected function getVersionFiles(ThemeVersion $version): array
    {
        $files = Storage::files('themes/'.$version->theme_id.'/'.$version->id);
        $totalSize = array_sum(array_map(fn($file) => Storage::size($file), $files));
        
        $this->rollbackRecord->update([
            'file_count' => count($files),
            'file_size_kb' => round($totalSize / 1024),
            'system_metrics' => [
                'memory_usage' => memory_get_usage(true),
                'load_avg' => sys_getloadavg()[0],
                'php_version' => PHP_VERSION
            ]
        ]);
        
        return $files;
    }

    protected function createBackup(ThemeVersion $version): string
    {
        $backupPath = 'theme-backups/'.$this->theme->id.'/'.now()->format('Y-m-d_H-i-s');
        Storage::makeDirectory($backupPath);
        
        foreach ($this->getVersionFiles($version) as $file) {
            Storage::copy($file, $backupPath.'/'.basename($file));
        }
        
        return $backupPath;
    }

    protected function restoreFiles(array $files): void
    {
        $activePath = 'themes/'.$this->theme->id.'/active';
        
        foreach ($files as $file) {
            $filename = basename($file);
            Storage::put($activePath.'/'.$filename, Storage::get($file));
        }
    }

    protected function getFileChanges(array $files): array
    {
        return array_map(fn($file) => [
            'filename' => basename($file),
            'size' => Storage::size($file),
            'modified' => Storage::lastModified($file)
        ], $files);
    }
}
