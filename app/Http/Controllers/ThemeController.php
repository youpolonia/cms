<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use App\Services\ThemeVersionService;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    protected $versionService;

    public function __construct(ThemeVersionService $versionService)
    {
        $this->versionService = $versionService;
    }

    // Existing methods...

    public function checkUpdates(Theme $theme)
    {
        $success = $this->versionService->checkForUpdates($theme);
        
        return back()->with([
            'status' => $success ? 'version-check-success' : 'version-check-failed',
            'message' => $success 
                ? 'Version check completed successfully' 
                : 'Failed to check for updates'
        ]);
    }

    public function update(Theme $theme)
    {
        $success = $this->versionService->updateTheme($theme);
        
        return back()->with([
            'status' => $success ? 'update-success' : 'update-failed',
            'message' => $success 
                ? 'Theme updated successfully' 
                : 'Failed to update theme'
        ]);
    }

    public function rollback(Theme $theme, Request $request)
    {
        $success = $this->versionService->rollbackTheme(
            $theme, 
            $request->input('version')
        );
        
        return back()->with([
            'status' => $success ? 'rollback-success' : 'rollback-failed',
            'message' => $success 
                ? 'Theme rolled back successfully' 
                : 'Failed to rollback theme'
        ]);
    }

    public function versionHistory(Theme $theme)
    {
        return view('themes.version-history', [
            'theme' => $theme,
            'history' => $theme->version_history
        ]);
    }
}
