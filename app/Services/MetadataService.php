<?php

namespace App\Services;

use App\Models\AnalyticsExport;
use App\Models\Metadata;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MetadataService
{
    protected $searchableFields = [
        'name', 'description', 'tags', 'owner', 'data_source'
    ];

    /**
     * Store metadata for an export
     */
    public function storeMetadata(int $exportId, array $metadata): Metadata
    {
        $export = AnalyticsExport::findOrFail($exportId);

        return Metadata::updateOrCreate(
            ['export_id' => $exportId],
            [
                'name' => $metadata['name'] ?? $export->name,
                'description' => $metadata['description'] ?? null,
                'tags' => $this->normalizeTags($metadata['tags'] ?? []),
                'owner' => $metadata['owner'] ?? null,
                'data_source' => $metadata['data_source'] ?? null,
                'sensitivity' => $metadata['sensitivity'] ?? 'public',
                'retention_period' => $metadata['retention_period'] ?? 365,
                'version' => $this->getNextVersion($exportId),
                'custom_fields' => $metadata['custom_fields'] ?? [],
                'governance_rules' => $metadata['governance_rules'] ?? $this->getDefaultGovernanceRules()
            ]
        );
    }

    /**
     * Get metadata for an export
     */
    public function getMetadata(int $exportId): ?Metadata
    {
        return Metadata::where('export_id', $exportId)
            ->orderBy('version', 'desc')
            ->first();
    }

    /**
     * Search metadata
     */
    public function search(string $query, array $filters = []): array
    {
        $search = Metadata::query();

        // Full-text search
        if (!empty($query)) {
            $search->where(function($q) use ($query) {
                foreach ($this->searchableFields as $field) {
                    $q->orWhere($field, 'like', "%{$query}%");
                }
            });
        }

        // Apply filters
        foreach ($filters as $field => $value) {
            if (in_array($field, ['tags', 'sensitivity', 'owner'])) {
                if ($field === 'tags') {
                    $search->whereJsonContains('tags', $value);
                } else {
                    $search->where($field, $value);
                }
            }
        }

        return $search->with('export')
            ->orderBy('updated_at', 'desc')
            ->paginate(20)
            ->toArray();
    }

    /**
     * Apply governance rules to an export
     */
    public function applyGovernance(int $exportId): bool
    {
        $metadata = $this->getMetadata($exportId);
        if (!$metadata) {
            return false;
        }

        $rules = $metadata->governance_rules;
        $export = $metadata->export;

        // Apply access controls
        if (isset($rules['access_control'])) {
            $this->applyAccessControl($export, $rules['access_control']);
        }

        // Apply retention policy
        if (isset($rules['retention_policy'])) {
            $this->applyRetentionPolicy($export, $rules['retention_policy']);
        }

        // Apply data masking if needed
        if ($metadata->sensitivity === 'confidential') {
            $this->applyDataMasking($export);
        }

        return true;
    }

    protected function applyAccessControl(AnalyticsExport $export, array $rules): void
    {
        // Implement access control logic based on rules
        // This would integrate with your permission system
    }

    protected function applyRetentionPolicy(AnalyticsExport $export, array $policy): void
    {
        // Implement retention policy logic
        if ($policy['action'] === 'archive' && $export->created_at->diffInDays() > $policy['days']) {
            // Archive the export
        } elseif ($policy['action'] === 'delete') {
            // Delete the export
        }
    }

    protected function applyDataMasking(AnalyticsExport $export): void
    {
        // Implement data masking for sensitive exports
    }

    protected function getNextVersion(int $exportId): int
    {
        $latest = Metadata::where('export_id', $exportId)
            ->orderBy('version', 'desc')
            ->first();

        return $latest ? $latest->version + 1 : 1;
    }

    protected function normalizeTags(array $tags): array
    {
        return array_map(function($tag) {
            return Str::slug($tag);
        }, $tags);
    }

    protected function getDefaultGovernanceRules(): array
    {
        return [
            'access_control' => [
                'level' => 'role-based',
                'roles' => ['admin', 'analyst']
            ],
            'retention_policy' => [
                'action' => 'archive',
                'days' => 365
            ],
            'data_protection' => [
                'masking' => false,
                'encryption' => true
            ]
        ];
    }

    /**
     * Get metadata history for an export
     */
    public function getHistory(int $exportId): array
    {
        return Metadata::where('export_id', $exportId)
            ->orderBy('version', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get metadata schema
     */
    public function getSchema(): array
    {
        return [
            'required' => ['name', 'owner', 'data_source'],
            'optional' => ['description', 'tags', 'custom_fields'],
            'governance' => ['sensitivity', 'retention_period']
        ];
    }
}