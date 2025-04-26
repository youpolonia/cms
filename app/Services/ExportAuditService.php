<?php

namespace App\Services;

use App\Models\AnalyticsExport;
use App\Models\ExportAuditLog;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ExportAuditService
{
    /**
     * Log an export access event
     */
    public function logAccess(int $exportId, string $action): ExportAuditLog
    {
        return ExportAuditLog::create([
            'export_id' => $exportId,
            'user_id' => Auth::id(),
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'accessed_at' => Carbon::now()
        ]);
    }

    /**
     * Get audit trail for an export
     */
    public function getAuditTrail(int $exportId)
    {
        return ExportAuditLog::with('user')
            ->where('export_id', $exportId)
            ->orderBy('accessed_at', 'desc')
            ->get();
    }

    /**
     * Generate compliance report for a date range
     */
    public function generateComplianceReport(Carbon $startDate, Carbon $endDate)
    {
        return [
            'total_exports' => AnalyticsExport::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_accesses' => ExportAuditLog::whereBetween('accessed_at', [$startDate, $endDate])->count(),
            'users' => ExportAuditLog::select('user_id')
                ->with('user')
                ->whereBetween('accessed_at', [$startDate, $endDate])
                ->distinct()
                ->get()
                ->pluck('user'),
            'most_accessed' => AnalyticsExport::withCount(['auditLogs as accesses' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('accessed_at', [$startDate, $endDate]);
            }])
            ->orderBy('accesses', 'desc')
            ->take(5)
            ->get(),
            'unusual_activity' => $this->detectUnusualActivity($startDate, $endDate)
        ];
    }

    /**
     * Detect unusual access patterns
     */
    protected function detectUnusualActivity(Carbon $startDate, Carbon $endDate)
    {
        // Implement anomaly detection logic
        return ExportAuditLog::whereBetween('accessed_at', [$startDate, $endDate])
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > ?', [10]) // Threshold for unusual activity
            ->get();
    }

    /**
     * Purge old audit logs
     */
    public function purgeOldLogs(int $days = 365)
    {
        $cutoffDate = Carbon::now()->subDays($days);
        return ExportAuditLog::where('accessed_at', '<', $cutoffDate)->delete();
    }
}