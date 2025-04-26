<?php

namespace App\Services;

use App\Models\AnalyticsExport;
use App\Models\ExportBackup;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessExportRestore;
use Carbon\Carbon;

class ExportRecoveryService
{
    protected $backupDisk = 'export_backups';
    protected $primaryDisk = 'exports';
    protected $failoverDisk = 'export_failover';

    /**
     * Create backup for an export
     */
    public function createBackup(int $exportId): ExportBackup
    {
        $export = AnalyticsExport::findOrFail($exportId);

        // Copy export file to backup location
        $backupPath = $this->generateBackupPath($export);
        Storage::disk($this->backupDisk)->put(
            $backupPath,
            Storage::disk($this->primaryDisk)->get($export->file_path)
        );

        // Create backup record
        return ExportBackup::create([
            'export_id' => $exportId,
            'backup_path' => $backupPath,
            'backup_disk' => $this->backupDisk,
            'file_size' => $export->file_size,
            'metadata' => [
                'original_path' => $export->file_path,
                'backup_type' => 'scheduled',
                'created_at' => now()
            ]
        ]);
    }

    protected function generateBackupPath(AnalyticsExport $export): string
    {
        return sprintf(
            'exports/%s/%s/%s',
            $export->created_at->format('Y/m/d'),
            $export->id,
            basename($export->file_path)
        );
    }

    /**
     * Restore export from backup
     */
    public function restoreExport(int $backupId, bool $queue = true): bool
    {
        $backup = ExportBackup::findOrFail($backupId);

        if ($queue) {
            Queue::push(new ProcessExportRestore($backupId));
            return true;
        }

        return $this->performRestore($backup);
    }

    protected function performRestore(ExportBackup $backup): bool
    {
        try {
            $export = $backup->export;

            // Copy backup to primary location
            Storage::disk($this->primaryDisk)->put(
                $export->file_path,
                Storage::disk($backup->backup_disk)->get($backup->backup_path)
            );

            // Update export record
            $export->update([
                'restored_at' => now(),
                'restored_from' => $backup->id
            ]);

            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    /**
     * Initiate failover procedure
     */
    public function initiateFailover(int $exportId): bool
    {
        $export = AnalyticsExport::findOrFail($exportId);

        // Check if file exists in failover location
        if (Storage::disk($this->failoverDisk)->exists($export->file_path)) {
            // Copy from failover to primary
            Storage::disk($this->primaryDisk)->put(
                $export->file_path,
                Storage::disk($this->failoverDisk)->get($export->file_path)
            );

            $export->update([
                'failover_used' => true,
                'last_failover_at' => now()
            ]);

            return true;
        }

        return false;
    }

    /**
     * Get available backups for an export
     */
    public function getBackups(int $exportId)
    {
        return ExportBackup::where('export_id', $exportId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get recovery status for an export
     */
    public function getRecoveryStatus(int $exportId): array
    {
        $export = AnalyticsExport::with('backups')->findOrFail($exportId);

        return [
            'export_id' => $exportId,
            'primary_exists' => Storage::disk($this->primaryDisk)->exists($export->file_path),
            'failover_exists' => Storage::disk($this->failoverDisk)->exists($export->file_path),
            'backup_count' => $export->backups->count(),
            'latest_backup' => $export->backups->first()?->created_at,
            'last_restored' => $export->restored_at,
            'failover_used' => $export->failover_used
        ];
    }

    /**
     * Verify backup integrity
     */
    public function verifyBackup(int $backupId): array
    {
        $backup = ExportBackup::findOrFail($backupId);

        $exists = Storage::disk($backup->backup_disk)->exists($backup->backup_path);
        $size = $exists ? Storage::disk($backup->backup_disk)->size($backup->backup_path) : 0;

        return [
            'backup_id' => $backupId,
            'exists' => $exists,
            'size_matches' => $size === $backup->file_size,
            'verified_at' => now(),
            'status' => $exists && ($size === $backup->file_size) ? 'valid' : 'invalid'
        ];
    }

    /**
     * Prune old backups
     */
    public function pruneBackups(int $daysToKeep = 30): array
    {
        $cutoffDate = Carbon::now()->subDays($daysToKeep);
        $backups = ExportBackup::where('created_at', '<', $cutoffDate)->get();

        $results = [
            'total' => $backups->count(),
            'deleted' => 0,
            'failed' => 0
        ];

        foreach ($backups as $backup) {
            try {
                Storage::disk($backup->backup_disk)->delete($backup->backup_path);
                $backup->delete();
                $results['deleted']++;
            } catch (\Exception $e) {
                report($e);
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Replicate to failover location
     */
    public function replicateToFailover(int $exportId): bool
    {
        $export = AnalyticsExport::findOrFail($exportId);

        if (!Storage::disk($this->primaryDisk)->exists($export->file_path)) {
            return false;
        }

        Storage::disk($this->failoverDisk)->put(
            $export->file_path,
            Storage::disk($this->primaryDisk)->get($export->file_path)
        );

        $export->update([
            'last_replicated_at' => now()
        ]);

        return true;
    }
}