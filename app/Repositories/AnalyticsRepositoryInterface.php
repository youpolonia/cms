<?php

namespace App\Repositories;

use App\Models\AnalyticsExport;
use Illuminate\Support\Collection;

interface AnalyticsRepositoryInterface
{
    /**
     * Create new analytics export record
     */
    public function createExport(array $data): AnalyticsExport;

    /**
     * Get view statistics for content version
     */
    public function getViewStats(int $versionId): array;

    /**
     * Get comparison statistics between two versions
     */
    public function getComparisonStats(int $version1Id, int $version2Id): array;

    /**
     * Get recent exports
     */
    public function getRecentExports(int $limit = 5): Collection;

    /**
     * Get content metrics for dashboard
     */
    public function getContentMetrics(string $range): array;

    /**
     * Get views trend data for chart
     */
    public function getViewsTrendData(string $range): array;

    /**
     * Get top performing content
     */
    public function getTopContent(string $range): array;
}