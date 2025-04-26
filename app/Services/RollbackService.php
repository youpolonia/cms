<?php

namespace App\Services;

use App\Models\ThemeVersion;
use App\Models\ThemeVersionRollback;
use App\Models\User;
use App\Notifications\ThemeRollbackNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RollbackService
{
    public function initiateRollback(ThemeVersion $version): ThemeVersionRollback
    {
        $rollback = ThemeVersionRollback::create([
            'theme_version_id' => $version->id,
            'status' => 'pending',
            'rollback_to_version_id' => $version->previous_version_id
        ]);

        $this->sendRollbackNotifications($rollback);
        
        return $rollback;
    }

    public function executeRollback(ThemeVersionRollback $rollback): bool
    {
        try {
            $version = $rollback->version;
            $targetVersion = $rollback->rollbackToVersion;

            // Backup current version files
            $this->backupVersionFiles($version);

            // Restore target version files
            $this->restoreVersionFiles($targetVersion, $version);

            $rollback->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Rollback failed: " . $e->getMessage());
            $rollback->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            return false;
        }
    }

    protected function backupVersionFiles(ThemeVersion $version)
    {
        $backupPath = "themes/{$version->theme_id}/backups/{$version->id}_" . now()->format('YmdHis');
        Storage::copyDirectory("themes/{$version->theme_id}/versions/{$version->id}", $backupPath);
    }

    protected function restoreVersionFiles(ThemeVersion $source, ThemeVersion $destination)
    {
        Storage::deleteDirectory("themes/{$destination->theme_id}/versions/{$destination->id}");
        Storage::copyDirectory(
            "themes/{$source->theme_id}/versions/{$source->id}",
            "themes/{$destination->theme_id}/versions/{$destination->id}"
        );
    }

    public function sendRollbackNotifications(ThemeVersionRollback $rollback)
    {
        try {
            $theme = $rollback->theme;
            
            if (!$theme->rollback_notifications_enabled) {
                Log::info("Rollback notifications disabled for theme {$theme->id}");
                return;
            }

            $users = User::query()
                ->whereIn('id', $theme->rollback_notification_users ?? [])
                ->orWhereHas('roles', function($query) use ($theme) {
                    $query->whereIn('id', $theme->rollback_notification_roles ?? []);
                })
                ->get();

            $notification = new ThemeRollbackNotification($rollback);

            foreach ($users as $user) {
                try {
                    $user->notify($notification->onQueue('notifications'));
                } catch (\Exception $e) {
                    Log::error("Failed to send rollback notification to user {$user->id}: " . $e->getMessage());
                }
            }

            Log::info("Sent rollback notifications for theme {$theme->id} to " . count($users) . " users");
            
        } catch (\Exception $e) {
            Log::error("Rollback notification error: " . $e->getMessage());
            throw $e;
        }
    }
}
