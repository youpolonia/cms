<?php

namespace App\Services;

use App\Models\AnalyticsExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Log;

class ExportOptimizationService
{
    protected $cacheTtl = 3600; // 1 hour
    protected $queryThreshold = 500; // ms
    protected $slowQueryThreshold = 2000; // ms

    /**
     * Optimize export query
     */
    public function optimizeQuery(Builder $query, int $exportId): Builder
    {
        $export = AnalyticsExport::find($exportId);
        $optimized = false;

        // Check for cached query plan
        $cacheKey = "export_query_plan:{$exportId}";
        if (Cache::has($cacheKey)) {
            $optimizations = Cache::get($cacheKey);
            return $this->applyOptimizations($query, $optimizations);
        }

        // Analyze query without execution
        $analysis = $this->analyzeQuery($query);

        // Apply optimizations based on analysis
        $optimizedQuery = $this->applyQueryOptimizations($query, $analysis);
        
        if ($optimizedQuery !== $query) {
            $optimized = true;
            $query = $optimizedQuery;
        }

        // Cache optimizations if effective
        if ($optimized && $export) {
            Cache::put($cacheKey, $analysis['optimizations'], $this->cacheTtl);
        }

        return $query;
    }

    protected function analyzeQuery(Builder $query): array
    {
        $explain = DB::select(DB::raw("EXPLAIN {$query->toSql()}"), $query->getBindings());
        $analysis = [
            'type' => $explain[0]->type ?? null,
            'possible_keys' => $explain[0]->possible_keys ?? null,
            'key' => $explain[0]->key ?? null,
            'rows' => $explain[0]->rows ?? null,
            'extra' => $explain[0]->Extra ?? null,
            'optimizations' => []
        ];

        // Detect full table scans
        if ($analysis['type'] === 'ALL' && $analysis['rows'] > 1000) {
            $analysis['optimizations'][] = 'add_index';
        }

        // Detect missing indexes
        if (empty($analysis['key']) && $analysis['rows'] > 100) {
            $analysis['optimizations'][] = 'add_index';
        }

        // Detect filesort
        if (strpos($analysis['extra'], 'filesort') !== false) {
            $analysis['optimizations'][] = 'optimize_order_by';
        }

        return $analysis;
    }

    protected function applyQueryOptimizations(Builder $query, array $analysis): Builder
    {
        $optimizedQuery = clone $query;

        foreach ($analysis['optimizations'] as $optimization) {
            switch ($optimization) {
                case 'add_index':
                    // In a real implementation, this would recommend indexes
                    // rather than modifying the query directly
                    break;
                case 'optimize_order_by':
                    $optimizedQuery->reorder();
                    break;
                case 'limit_results':
                    if (!$optimizedQuery->getQuery()->limit) {
                        $optimizedQuery->limit(1000);
                    }
                    break;
            }
        }

        return $optimizedQuery;
    }

    /**
     * Cache export data
     */
    public function cacheExport(int $exportId, $data, ?string $cacheKey = null): void
    {
        $cacheKey = $cacheKey ?: "export_data:{$exportId}";
        Cache::put($cacheKey, $data, $this->cacheTtl);
    }

    /**
     * Get cached export
     */
    public function getCachedExport(int $exportId, ?string $cacheKey = null)
    {
        $cacheKey = $cacheKey ?: "export_data:{$exportId}";
        return Cache::get($cacheKey);
    }

    /**
     * Recommend indexes for exports
     */
    public function recommendIndexes(int $exportId): array
    {
        $export = AnalyticsExport::findOrFail($exportId);
        $recommendations = [];

        // Analyze common query patterns
        $queries = DB::table('query_logs')
            ->where('export_id', $exportId)
            ->orderBy('execution_count', 'desc')
            ->limit(5)
            ->get();

        foreach ($queries as $query) {
            $analysis = $this->analyzeQueryPattern($query->sql);
            if (!empty($analysis['recommended_indexes'])) {
                $recommendations[] = [
                    'query' => $query->sql,
                    'execution_count' => $query->execution_count,
                    'average_time' => $query->average_time,
                    'indexes' => $analysis['recommended_indexes']
                ];
            }
        }

        return $recommendations;
    }

    protected function analyzeQueryPattern(string $sql): array
    {
        // Simplified analysis - in a real implementation this would parse the SQL
        // and identify columns used in WHERE, JOIN, ORDER BY clauses
        return [
            'recommended_indexes' => [
                [
                    'table' => 'analytics_exports',
                    'columns' => ['created_at', 'tenant_id'],
                    'type' => 'compound'
                ]
            ]
        ];
    }

    /**
     * Monitor query performance
     */
    public function monitorQueryPerformance(Builder $query, int $exportId): array
    {
        $start = microtime(true);
        $results = $query->get();
        $duration = (microtime(true) - $start) * 1000; // ms

        $performance = [
            'export_id' => $exportId,
            'execution_time' => $duration,
            'result_count' => count($results),
            'status' => $duration < $this->queryThreshold ? 'optimal' : 
                      ($duration < $this->slowQueryThreshold ? 'suboptimal' : 'slow')
        ];

        if ($performance['status'] !== 'optimal') {
            Log::warning("Suboptimal export query", [
                'export_id' => $exportId,
                'duration' => $duration,
                'query' => $query->toSql()
            ]);
        }

        return $performance;
    }

    /**
     * Pre-warm cache for frequent exports
     */
    public function prewarmCache(int $exportId): bool
    {
        $export = AnalyticsExport::findOrFail($exportId);
        
        if ($export->cache_warmed_at && $export->cache_warmed_at->gt(now()->subHours(1))) {
            return false;
        }

        $data = $export->getExportData();
        $this->cacheExport($exportId, $data);
        $export->update(['cache_warmed_at' => now()]);

        return true;
    }

    /**
     * Set cache TTL
     */
    public function setCacheTtl(int $seconds): void
    {
        $this->cacheTtl = $seconds;
    }

    /**
     * Set query thresholds
     */
    public function setQueryThresholds(int $optimal, int $slow): void
    {
        $this->queryThreshold = $optimal;
        $this->slowQueryThreshold = $slow;
    }
}