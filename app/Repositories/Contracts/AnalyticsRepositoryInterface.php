<?php

namespace App\Repositories\Contracts;

interface AnalyticsRepositoryInterface
{
    public function createExport(array $data): array;
    public function getViewStats(int $versionId): array;
    public function getComparisonStats(int $version1Id, int $version2Id): array;
    public function getRecentExports(int $limit = 5): array;
}