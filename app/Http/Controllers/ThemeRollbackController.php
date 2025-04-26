<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use App\Models\ThemeVersion;
use App\Models\ThemeVersionRollback;
use App\Jobs\RollbackThemeVersion;
use App\Notifications\ThemeRollbackNotification;
use App\Notifications\ThemeRollbackCompleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ThemeRollbackController extends Controller
{
    public function confirm(Theme $theme, ThemeVersion $version)
    {
        $this->authorize('rollback', $version);

        $previousVersions = $theme->versions()
            ->where('id', '<', $version->id)
            ->orderBy('id', 'desc')
            ->get();

        if ($previousVersions->isEmpty()) {
            return redirect()
                ->route('themes.versions.show', [$theme, $version])
                ->with('error', 'No previous versions available for rollback');
        }

        // Get file changes between current and selected version
        $fileChanges = $this->getFileChanges($version, $previousVersions->first());

        return view('themes.versions.rollback-confirm', [
            'theme' => $theme,
            'currentVersion' => $version,
            'rollbackVersion' => $previousVersions->first(),
            'fileChanges' => $fileChanges,
        ]);
    }

    public function execute(Request $request, Theme $theme, ThemeVersion $version)
    {
        $this->authorize('rollback', $version);

        $rollbackVersion = ThemeVersion::findOrFail($request->rollback_version_id);

        // Create rollback record
        $rollback = ThemeVersionRollback::create([
            'theme_version_id' => $version->id,
            'rollback_to_version_id' => $rollbackVersion->id,
            'initiated_by' => Auth::id(),
            'status' => 'pending',
        ]);

        // Dispatch rollback job
        RollbackThemeVersion::dispatch($rollback, Auth::user());

        // Send notifications
        $notification = new ThemeRollbackNotification($rollback);
        
        // Notify initiating user
        Auth::user()->notify($notification);
        
        // Notify theme administrators
        $theme->administrators->each->notify($notification);
        
        // Notify system administrators
        User::role('admin')->each->notify($notification);

        return redirect()
            ->route('themes.versions.show', [$theme, $version])
            ->with('success', 'Theme rollback has been initiated. You will be notified when complete.');
    }

    public function history(Theme $theme, ThemeVersion $version)
    {
        $this->authorize('view', $version);

        $rollbacks = ThemeVersionRollback::with(['rollbackToVersion', 'initiatedBy'])
            ->where('theme_version_id', $version->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('themes.versions.rollback-history', [
            'theme' => $theme,
            'currentVersion' => $version,
            'rollbacks' => $rollbacks,
        ]);
    }

    public function details(Theme $theme, ThemeVersionRollback $rollback)
    {
        $this->authorize('view', $rollback->version);

        $fileChanges = $this->getFileChanges($rollback->version, $rollback->rollbackToVersion);

        return view('themes.versions.rollback-details', [
            'theme' => $theme,
            'rollback' => $rollback,
            'fileChanges' => $fileChanges,
        ]);
    }

    protected function getFileChanges(ThemeVersion $current, ThemeVersion $rollbackTo)
    {
        $currentFiles = json_decode(Storage::get($current->files_manifest_path), true);
        $rollbackFiles = json_decode(Storage::get($rollbackTo->files_manifest_path), true);

        $changes = [];

        // Check for modified files
        foreach ($currentFiles as $path => $currentHash) {
            if (isset($rollbackFiles[$path]) && $rollbackFiles[$path] !== $currentHash) {
                $changes[] = [
                    'path' => $path,
                    'type' => 'modified',
                ];
            }
        }

        // Check for added files (in current but not in rollback)
        foreach ($currentFiles as $path => $hash) {
            if (!isset($rollbackFiles[$path])) {
                $changes[] = [
                    'path' => $path,
                    'type' => 'added',
                ];
            }
        }

        // Check for deleted files (in rollback but not in current)
        foreach ($rollbackFiles as $path => $hash) {
            if (!isset($currentFiles[$path])) {
                $changes[] = [
                    'path' => $path,
                    'type' => 'deleted',
                ];
            }
        }

        return $changes;
    }
}
