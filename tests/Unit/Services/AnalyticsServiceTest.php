<?php

namespace Tests\Unit\Services;

use App\Models\AnalyticsExport;

use App\Repositories\AnalyticsRepositoryInterface;
use App\Services\AnalyticsService;
use Mockery;
use Tests\TestCase;

class AnalyticsServiceTest extends TestCase
{
    private $analyticsRepository;
    private $analyticsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyticsRepository = Mockery::mock(AnalyticsRepositoryInterface::class);
        $this->analyticsService = new AnalyticsService($this->analyticsRepository);
    }

    public function testCreateExportSuccess()
    {
        $exportData = ['name' => 'Test Export', 'format' => 'csv'];
        $export = new AnalyticsExport($exportData);
        $export->id = 1;
        $export->status = 'pending';
        $export->created_at = now();
        $export->updated_at = now();
        
        $expectedResult = [
            'success' => true,
            'export' => $export->toArray()
        ];
        
        $this->analyticsRepository
            ->shouldReceive('createExport')
            ->with($exportData)
            ->andReturn($export);

        $result = $this->analyticsService->createExport($exportData);
        $this->assertEquals($expectedResult, [
            'success' => $result['success'],
            'export' => $result['export']->toArray()
        ]);
    }

    public function testCreateExportFailure()
    {
        $this->analyticsRepository
            ->shouldReceive('createExport')
            ->andThrow(new \Exception('Database error'));

        $result = $this->analyticsService->createExport([]);
        $this->assertFalse($result['success']);
        $this->assertEquals('Failed to create export', $result['error']);
    }

    public function testGetVersionViewStats()
    {
        $versionId = 1;
        $expectedStats = ['total_views' => 100, 'unique_visitors' => 50];

        $this->analyticsRepository
            ->shouldReceive('getViewStats')
            ->with($versionId)
            ->andReturn($expectedStats);

        $stats = $this->analyticsService->getVersionViewStats($versionId);
        $this->assertEquals($expectedStats, $stats);
    }

    public function testCompareVersions()
    {
        $version1Id = 1;
        $version2Id = 2;
        $expectedComparison = [
            'version1' => ['total_views' => 100],
            'version2' => ['total_views' => 80],
            'comparison' => ['views_diff' => 20]
        ];

        $this->analyticsRepository
            ->shouldReceive('getComparisonStats')
            ->with($version1Id, $version2Id)
            ->andReturn($expectedComparison);

        $comparison = $this->analyticsService->compareVersions($version1Id, $version2Id);
        $this->assertEquals($expectedComparison, $comparison);
    }

    public function testGetRecentExports()
    {
        $expectedExports = [
            ['id' => 1, 'name' => 'Export 1'],
            ['id' => 2, 'name' => 'Export 2']
        ];

        $collection = new \Illuminate\Support\Collection($expectedExports);
        
        $this->analyticsRepository
            ->shouldReceive('getRecentExports')
            ->with(5)
            ->andReturn($collection);

        $exports = $this->analyticsService->getRecentExports();
        $this->assertEquals(['exports' => $expectedExports, 'count' => 5], [
            'exports' => $exports['exports']->toArray(),
            'count' => $exports['count']
        ]);
    }
    public function testUpdateExportStatus()
    {
        $exportId = 1;
        $newStatus = 'completed';
        $export = new AnalyticsExport(['status' => $newStatus]);
        $export->id = $exportId;

        $this->analyticsRepository
            ->shouldReceive('updateExportStatus')
            ->with($exportId, $newStatus)
            ->andReturn($export);

        $result = $this->analyticsService->updateExportStatus($exportId, $newStatus);
        $this->assertEquals($newStatus, $result->status);
    }

    public function testGenerateExportFile()
    {
        $exportId = 1;
        $filePath = '/exports/test.csv';
        $export = new AnalyticsExport(['file_path' => $filePath]);
        $export->id = $exportId;

        $this->analyticsRepository
            ->shouldReceive('generateExportFile')
            ->with($exportId)
            ->andReturn($export);

        $result = $this->analyticsService->generateExportFile($exportId);
        $this->assertEquals($filePath, $result->file_path);
    }

    public function testTrackExportEvent()
    {
        $exportId = 1;
        $eventType = 'download';
        $expectedResult = ['success' => true];

        $this->analyticsRepository
            ->shouldReceive('trackExportEvent')
            ->with($exportId, $eventType)
            ->andReturn($expectedResult);

        $result = $this->analyticsService->trackExportEvent($exportId, $eventType);
        $this->assertEquals($expectedResult, $result);
    }

    public function testSendExportNotification()
    {
        $exportId = 1;
        $userId = 1;
        $expectedResult = ['success' => true];

        $this->analyticsRepository
            ->shouldReceive('sendExportNotification')
            ->with($exportId, $userId)
            ->andReturn($expectedResult);

        $result = $this->analyticsService->sendExportNotification($exportId, $userId);
        $this->assertEquals($expectedResult, $result);
    }
}