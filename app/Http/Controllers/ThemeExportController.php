<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use App\Models\ThemeVersion;
use App\Services\ThemeService;
use Illuminate\Http\Request;
use App\Jobs\ProcessThemeExport;
use App\Notifications\ThemeExportReady;

class ThemeExportController extends Controller
{
    protected $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
    }

    public function exportTheme(Theme $theme)
    {
        $path = $this->themeService->exportTheme($theme);
        return response()->download($path)->deleteFileAfterSend();
    }

    public function batchExport(Request $request)
    {
        $request->validate([
            'versions' => 'required|array',
            'theme_id' => 'required|exists:themes,id'
        ]);

        $theme = Theme::find($request->theme_id);
        $versions = ThemeVersion::whereIn('id', $request->versions)->get();

        foreach ($versions as $version) {
            ProcessThemeExport::dispatch($version, 'zip', auth()->user());
        }

        return response()->json(['message' => 'Batch export queued']);
    }

    public function exportVersion(ThemeVersion $version, Request $request)
    {
        $format = $request->get('format', 'zip');
        $path = $this->themeService->exportVersion($version, $format);
        
        if ($request->get('queue', false)) {
            ProcessThemeExport::dispatch($version, $format, auth()->user());
            return response()->json(['message' => 'Export queued']);
        }

        return response()->download($path)->deleteFileAfterSend();
    }

    public function exportAllVersions(Theme $theme)
    {
        $path = $this->themeService->exportAllVersions($theme);
        return response()->download($path)->deleteFileAfterSend();
    }
}