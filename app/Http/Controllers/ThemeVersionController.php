<?php

namespace App\Http\Controllers;

use App\Models\ThemeVersion;
use App\Models\ThemeVersionComparisonStat;
use App\Models\ThemeVersionRollback;
use App\Services\RollbackService;
use Illuminate\Http\Request;

class ThemeVersionController extends Controller
{
    protected $rollbackService;
    protected $comparisonService;

    public function __construct(RollbackService $rollbackService, ThemeVersionComparisonService $comparisonService)
    {
        $this->rollbackService = $rollbackService;
        $this->comparisonService = $comparisonService;
    }

    public function initiateRollback(Request $request, ThemeVersion $version)
    {
        $request->validate([
            'confirm' => 'required|boolean'
        ]);

        if (!$request->confirm) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rollback not confirmed'
            ], 400);
        }

        $rollback = $this->rollbackService->initiateRollback($version);

        return response()->json([
            'status' => 'success',
            'data' => $rollback
        ]);
    }

    public function executeRollback(ThemeVersionRollback $rollback)
    {
        $success = $this->rollbackService->executeRollback($rollback);

        return response()->json([
            'status' => $success ? 'success' : 'error',
            'data' => $rollback->fresh()
        ]);
    }

    public function getRollbackHistory(ThemeVersion $version)
    {
        $history = ThemeVersionRollback::where('theme_version_id', $version->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $history
        ]);
    }

    public function showComparisonStats(ThemeVersion $themeVersion, ThemeVersionComparisonStat $comparison)
    {
        // Verify the comparison belongs to this version
        if ($comparison->theme_version_id !== $themeVersion->id) {
            abort(404);
        }

        // Load the compared version relationship
        $comparison->load('comparedVersion');

        return view('themes.versions.compare-stats', [
            'comparison' => $comparison,
            'themeVersion' => $themeVersion
        ]);
    }

    public function history(Theme $theme)
    {
        $versions = $theme->versions()
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('theme.history', [
            'theme' => $theme,
            'versions' => $versions
        ]);
    }

    public function compare(ThemeVersion $versionA, ThemeVersion $versionB)
    {
        $comparison = $this->comparisonService->compareVersions($versionA, $versionB);

        return view('theme.compare', [
            'versionA' => $versionA,
            'versionB' => $versionB,
            'comparison' => $comparison
        ]);
    }

    public function stats(ThemeVersion $versionA, ThemeVersion $versionB)
    {
        $comparison = $this->comparisonService->compareVersions($versionA, $versionB);

        return view('theme.compare-stats', [
            'versionA' => $versionA,
            'versionB' => $versionB,
            'comparison' => $comparison
        ]);
    }

    public function compareVersions(Request $request, ThemeVersion $version1, ThemeVersion $version2)
    {
        $result = $this->comparisonService->compareVersions($version1, $version2);

        return response()->json([
            'status' => 'success',
            'data' => $result
        ]);
    }
}
