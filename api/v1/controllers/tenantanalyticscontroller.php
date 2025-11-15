<?php

namespace Api\v1\Controllers;

use Includes\Analytics\EventProcessor;
use Includes\Routing\Response;
use Includes\Services\TenantService;

class TenantAnalyticsController
{
    protected $processor;
    protected $tenantService;

    public function __construct()
    {
        $this->processor = new EventProcessor();
        $this->tenantService = new TenantService();
    }

    /**
     * Get tenant analytics data
     * @param string $tenantId UUID format
     * @param string $range Time range (1 DAY, 7 DAY, 30 DAY, 90 DAY)
     */
    public function getTenantAnalytics(string $tenantId, string $range = '7 DAY')
    {
        // Validate inputs
        if (!preg_match('/^[a-f0-9]{8}-([a-f0-9]{4}-){3}[a-f0-9]{12}$/i', $tenantId)) {
            return Response::json([
                'status' => 'error',
                'message' => 'Invalid tenant ID format',
                'data' => null
            ], 400);
        }

        if (!in_array($range, ['1 DAY', '7 DAY', '30 DAY', '90 DAY'])) {
            return Response::json([
                'status' => 'error',
                'message' => 'Invalid time range',
                'data' => null
            ], 400);
        }

        if (!$this->tenantService->validateTenantAccess($tenantId)) {
            return Response::json([
                'status' => 'error',
                'message' => 'Unauthorized tenant access',
                'data' => null
            ], 403);
        }

        try {
            $data = $this->processor->getTenantSummary($tenantId, $range);
            
            return Response::json([
                'status' => 'success',
                'data' => [
                    'events' => $data,
                    'date_range' => $range
                ],
                'error' => null
            ]);
            
        } catch (\Exception $e) {
            error_log("Tenant analytics error: " . $e->getMessage());
            return Response::json([
                'status' => 'error',
                'data' => null,
                'error' => 'Failed to fetch analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getComparisonReport($tenantIds = [], $dateRange = '7d')
    {
        if (empty($tenantIds)) {
            return Response::error('No tenant IDs provided', 400);
        }

        $validatedIds = [];
        foreach ($tenantIds as $id) {
            if ($this->tenantService->validateTenantAccess($id)) {
                $validatedIds[] = $id;
            }
        }

        if (empty($validatedIds)) {
            return Response::error('No valid tenant IDs provided', 400);
        }

        try {
            $report = $this->processor->getComparisonReport($validatedIds, $dateRange);
            return Response::json([
                'status' => 'success',
                'data' => $report,
                'meta' => [
                    'tenant_ids' => $validatedIds,
                    'date_range' => $dateRange
                ]
            ]);
        } catch (\Exception $e) {
            error_log("Comparison report error: " . $e->getMessage());
            return Response::error('Failed to generate comparison report', 500);
        }
    }
}
