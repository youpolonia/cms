<?php

namespace App\Http\Controllers;

use App\Models\ThemeVersionApproval;
use App\Models\ThemeApprovalWorkflow;
use App\Services\ThemeApprovalAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ThemeApprovalAnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(ThemeApprovalAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display approval analytics dashboard
     */
    public function index()
    {
        $approvals = ThemeVersionApproval::with(['version', 'workflow.steps'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('themes.analytics.approval', compact('approvals'));
    }

    /**
     * Export approval data
     */
    public function export(string $format): StreamedResponse
    {
        $filename = "theme-approval-export-".now()->format('Y-m-d').".{$format}";
        $data = ThemeVersionApproval::with(['version', 'workflow.steps'])->get();

        return $this->analyticsService->exportData($data, $filename, $format);
    }

    /**
     * Get approval stats for API
     */
    public function getApprovalStats(Request $request)
    {
        $validated = $request->validate([
            'theme_id' => 'required|exists:themes,id',
            'version_id' => 'required|exists:theme_versions,id',
            'workflow_id' => 'required|exists:theme_approval_workflows,id'
        ]);

        $stats = ThemeVersionApproval::where([
            'theme_id' => $validated['theme_id'],
            'version_id' => $validated['version_id'],
            'workflow_id' => $validated['workflow_id']
        ])->firstOrFail();

        return response()->json($stats);
    }

    /**
     * Get timeline data for approval workflow
     */
    public function getTimelineData(Request $request)
    {
        $validated = $request->validate([
            'workflow_id' => 'required|exists:theme_approval_workflows,id',
            'metric' => 'required|in:completion_time,step_duration,approval_rate'
        ]);

        $data = $this->analyticsService->getTimelineData(
            $validated['workflow_id'],
            $validated['metric']
        );

        return response()->json($data);
    }

    /**
     * Get approval completion rates
     */
    public function getCompletionRates(Request $request)
    {
        $validated = $request->validate([
            'workflow_id' => 'required|exists:theme_approval_workflows,id'
        ]);

        $rates = $this->analyticsService->getCompletionRates($validated['workflow_id']);

        return response()->json($rates);
    }
}
