<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\AnalyticsExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class TenantExportService
{
    protected $currentTenant;

    public function __construct()
    {
        $this->currentTenant = Tenant::current();
    }

    /**
     * Get exports for current tenant with proper isolation
     */
    public function getTenantExports()
    {
        return AnalyticsExport::where('tenant_id', $this->currentTenant->id)
            ->with(['user' => function ($query) {
                $query->where('tenant_id', $this->currentTenant->id);
            }])
            ->get();
    }

    /**
     * Create a new export with tenant context
     */
    public function createExport(array $data)
    {
        Gate::authorize('create-export', $this->currentTenant);

        return DB::transaction(function () use ($data) {
            $export = AnalyticsExport::create([
                ...$data,
                'tenant_id' => $this->currentTenant->id,
                'created_by' => auth()->id()
            ]);

            // Additional processing if needed

            return $export;
        });
    }

    /**
     * Get export data with proper tenant isolation
     */
    public function getExportData(int $exportId)
    {
        $export = AnalyticsExport::where('tenant_id', $this->currentTenant->id)
            ->findOrFail($exportId);

        Gate::authorize('view-export', $export);

        // Implement data partitioning logic here
        return $this->partitionExportData($export);
    }

    /**
     * Partition export data by tenant boundaries
     */
    protected function partitionExportData(AnalyticsExport $export)
    {
        // Implement tenant-specific data partitioning logic
        return [
            'export' => $export,
            'data' => $export->data()
                ->where('tenant_id', $this->currentTenant->id)
                ->get()
        ];
    }

    /**
     * Verify cross-tenant access permissions
     */
    public function verifyCrossTenantAccess($export, $tenant)
    {
        if ($export->tenant_id !== $tenant->id) {
            abort(403, 'Cross-tenant export access denied');
        }
    }
}