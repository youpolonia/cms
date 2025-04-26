<?php

namespace App\Http\Controllers;

use App\Services\ContentApprovalAnalyticsService;
use Illuminate\Http\Request;

class ApprovalAnalyticsController extends Controller
{
    public function __construct(
        protected ContentApprovalAnalyticsService $analyticsService
    ) {}

    public function index()
    {
        return response()->json([
            'stats' => $this->analyticsService->getApprovalStats(),
            'workflow' => $this->analyticsService->getWorkflowProgress(),
            'pending' => $this->analyticsService->getPendingApprovals()
        ]);
    }

    public function refresh()
    {
        $this->analyticsService->clearCache();
        
        return response()->json([
            'message' => 'Analytics cache cleared',
            'data' => $this->index()->getData()
        ]);
    }
}