<?php

namespace App\Services;

use App\Models\ModerationQueue;

class ModerationAnalyticsService
{
    public function trackModerationEvent($queueItem, $action, $metadata = [])
    {
        $analytics = $queueItem->analytics ?? [];
        
        $analytics['events'][] = [
            'action' => $action,
            'timestamp' => now()->toDateTimeString(),
            'metadata' => $metadata
        ];

        $queueItem->update([
            'analytics' => $analytics,
            'template_id' => $metadata['template_id'] ?? null,
            'template_type' => $metadata['template_type'] ?? null
        ]);

        return $queueItem;
    }

    public function getModerationStats($templateId = null)
    {
        $query = ModerationQueue::query();

        if ($templateId) {
            $query->where('template_id', $templateId);
        }

        return [
            'total_items' => $query->count(),
            'approved_count' => $query->where('status', 'approved')->count(),
            'rejected_count' => $query->where('status', 'rejected')->count(),
            'pending_count' => $query->where('status', 'pending')->count(),
            'average_time' => $this->calculateAverageProcessingTime($query)
        ];
    }

    protected function calculateAverageProcessingTime($query)
    {
        $results = $query->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg_time')
            ->whereNotNull('updated_at')
            ->first();

        return $results->avg_time ?? 0;
    }
}