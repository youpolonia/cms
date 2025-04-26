<?php

namespace App\Services;

use App\Repositories\AnalyticsRepositoryInterface;
use Illuminate\Support\Facades\Log;

class AnalyticsService
{
    protected $analyticsRepository;

    public function __construct(AnalyticsRepositoryInterface $analyticsRepository)
    {
        $this->analyticsRepository = $analyticsRepository;
    }

    public function createExport(array $data): array
    {
        try {
            $export = $this->analyticsRepository->createExport($data);
            return [
                'success' => true,
                'export' => $export
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create analytics export', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Failed to create export'
            ];
        }
    }

    public function getVersionViewStats(int $versionId): array
    {
        return $this->analyticsRepository->getViewStats($versionId);
    }

    public function compareVersions(int $version1Id, int $version2Id): array
    {
        return $this->analyticsRepository->getComparisonStats($version1Id, $version2Id);
    }

    public function getRecentExports(int $limit = 5): array
    {
        return [
            'exports' => $this->analyticsRepository->getRecentExports($limit),
            'count' => $limit
        ];
    }

    public function getContentAnalytics(string $range): array
    {
        $metrics = $this->analyticsRepository->getContentMetrics($range);
        $viewsData = $this->analyticsRepository->getViewsTrendData($range);
        $topContent = $this->analyticsRepository->getTopContent($range);

        return [
            'metrics' => [
                'totalViews' => $metrics['total_views'],
                'avgTimeOnPage' => $metrics['avg_time'],
                'bounceRate' => $metrics['bounce_rate'],
                'contentCount' => $metrics['content_count']
            ],
            'viewsData' => $viewsData,
            'topContentData' => $topContent
        ];
    }

    public function updateExportStatus(int $exportId, string $status): array
    {
        try {
            $updated = $this->analyticsRepository->updateExportStatus($exportId, $status);
            return [
                'success' => true,
                'export' => $updated
            ];
        } catch (\Exception $e) {
            Log::error('Failed to update export status', [
                'export_id' => $exportId,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => 'Failed to update export status'
            ];
        }
    }

    public function generateExportFile(int $exportId, string $format): array
    {
        try {
            $filePath = $this->analyticsRepository->generateExportFile($exportId, $format);
            return [
                'success' => true,
                'file_path' => $filePath
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate export file', [
                'export_id' => $exportId,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => 'Failed to generate export file'
            ];
        }
    }

    public function trackExportEvent(int $exportId, string $eventType, array $metadata = []): bool
    {
        try {
            $this->analyticsRepository->trackExportEvent($exportId, $eventType, $metadata);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to track export event', [
                'export_id' => $exportId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function sendExportNotification(int $exportId, string $notificationType): array
    {
        try {
            $sent = $this->analyticsRepository->sendExportNotification($exportId, $notificationType);
            return [
                'success' => $sent,
                'message' => $sent ? 'Notification sent' : 'Notification failed'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send export notification', [
                'export_id' => $exportId,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => 'Failed to send notification'
            ];
        }
    }
}
