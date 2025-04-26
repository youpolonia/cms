<?php

namespace App\Services;

use App\Models\AnalyticsExport;
use App\Models\ExportLineage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class DataLineageService
{
    /**
     * Track data lineage for an export
     */
    public function trackLineage(int $exportId, array $sourceIds, string $transformationType): ExportLineage
    {
        return DB::transaction(function () use ($exportId, $sourceIds, $transformationType) {
            $lineage = ExportLineage::create([
                'export_id' => $exportId,
                'transformation_type' => $transformationType,
                'metadata' => [
                    'created_at' => now(),
                    'created_by' => auth()->id()
                ]
            ]);

            $lineage->sources()->attach($sourceIds);

            return $lineage;
        });
    }

    /**
     * Get full lineage for an export
     */
    public function getLineage(int $exportId): Collection
    {
        return ExportLineage::with(['sources', 'export'])
            ->where('export_id', $exportId)
            ->get()
            ->map(function ($lineage) {
                return $this->formatLineageNode($lineage);
            });
    }

    /**
     * Format lineage node for visualization
     */
    protected function formatLineageNode(ExportLineage $lineage): array
    {
        return [
            'id' => $lineage->id,
            'export_id' => $lineage->export_id,
            'type' => $lineage->transformation_type,
            'sources' => $lineage->sources->map(function ($source) {
                return [
                    'id' => $source->id,
                    'name' => $source->name,
                    'type' => class_basename($source)
                ];
            }),
            'metadata' => $lineage->metadata
        ];
    }

    /**
     * Visualize data flows for an export
     */
    public function visualizeDataFlow(int $exportId): array
    {
        $lineage = $this->getLineage($exportId);

        return [
            'nodes' => $this->buildFlowNodes($lineage),
            'edges' => $this->buildFlowEdges($lineage)
        ];
    }

    /**
     * Build nodes for flow visualization
     */
    protected function buildFlowNodes(Collection $lineage): array
    {
        $nodes = [];
        
        foreach ($lineage as $item) {
            $nodes[] = [
                'id' => 'export_' . $item['export_id'],
                'label' => 'Export #' . $item['export_id'],
                'type' => 'export'
            ];

            foreach ($item['sources'] as $source) {
                $nodes[] = [
                    'id' => $source['type'] . '_' . $source['id'],
                    'label' => $source['name'],
                    'type' => $source['type']
                ];
            }
        }

        return array_values(array_unique($nodes, SORT_REGULAR));
    }

    /**
     * Build edges for flow visualization
     */
    protected function buildFlowEdges(Collection $lineage): array
    {
        $edges = [];
        
        foreach ($lineage as $item) {
            foreach ($item['sources'] as $source) {
                $edges[] = [
                    'from' => $source['type'] . '_' . $source['id'],
                    'to' => 'export_' . $item['export_id'],
                    'label' => $item['type']
                ];
            }
        }

        return $edges;
    }

    /**
     * Perform impact analysis for a data source
     */
    public function impactAnalysis(int $sourceId): array
    {
        return ExportLineage::whereHas('sources', function ($query) use ($sourceId) {
            $query->where('source_id', $sourceId);
        })
        ->with('export')
        ->get()
        ->map(function ($lineage) {
            return [
                'export_id' => $lineage->export_id,
                'export_name' => $lineage->export->name,
                'transformation_type' => $lineage->transformation_type,
                'created_at' => $lineage->created_at
            ];
        })
        ->toArray();
    }
}