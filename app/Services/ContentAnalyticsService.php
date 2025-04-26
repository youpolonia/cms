<?php

namespace App\Services;

use App\Models\Content;
use App\Models\ContentUserView;
use App\Models\AnalyticsExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ContentAnalyticsService
{
    public function recordView(Content $content, int $userId, array $context = []): ContentUserView
    {
        return ContentUserView::create([
            'content_id' => $content->id,
            'user_id' => $userId,
            'viewed_at' => now(),
            'duration_seconds' => $context['duration'] ?? null,
            'referrer' => $context['referrer'] ?? null,
            'device_type' => $context['device'] ?? null,
            'location' => $context['location'] ?? null,
            'ip_address' => $context['ip'] ?? null
        ]);
    }

    public function getContentViews(Content $content, array $filters = []): Collection
    {
        $query = ContentUserView::where('content_id', $content->id);

        if (isset($filters['date_from'])) {
            $query->where('viewed_at', '>=', Carbon::parse($filters['date_from']));
        }

        if (isset($filters['date_to'])) {
            $query->where('viewed_at', '<=', Carbon::parse($filters['date_to']));
        }

        if (isset($filters['min_duration'])) {
            $query->where('duration_seconds', '>=', $filters['min_duration']);
        }

        return $query->with('user')
            ->orderBy('viewed_at', 'desc')
            ->get();
    }

    public function getContentEngagementStats(Content $content, array $filters = []): array
    {
        $views = $this->getContentViews($content, $filters);

        return [
            'total_views' => $views->count(),
            'unique_viewers' => $views->groupBy('user_id')->count(),
            'average_duration' => $views->avg('duration_seconds'),
            'completion_rate' => $this->calculateCompletionRate($views, $content),
            'devices' => $views->groupBy('device_type')->map->count(),
            'referrers' => $views->groupBy('referrer')->map->count(),
            'locations' => $views->groupBy('location')->map->count(),
            'view_trends' => $this->calculateViewTrends($views)
        ];
    }

    protected function calculateCompletionRate(Collection $views, Content $content): ?float
    {
        if (!$content->duration_seconds) {
            return null;
        }

        $completedViews = $views->filter(function ($view) use ($content) {
            return $view->duration_seconds >= ($content->duration_seconds * 0.9);
        });

        return $views->isEmpty() ? null : ($completedViews->count() / $views->count()) * 100;
    }

    protected function calculateViewTrends(Collection $views): array
    {
        return $views->groupBy(function ($view) {
            return $view->viewed_at->format('Y-m-d');
        })->map->count();
    }

    public function getPopularContent(array $filters = []): Collection
    {
        $query = ContentUserView::query();

        if (isset($filters['date_from'])) {
            $query->where('viewed_at', '>=', Carbon::parse($filters['date_from']));
        }

        if (isset($filters['date_to'])) {
            $query->where('viewed_at', '<=', Carbon::parse($filters['date_to']));
        }

        return $query->select('content_id', DB::raw('count(*) as views'))
            ->groupBy('content_id')
            ->orderBy('views', 'desc')
            ->with('content')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'content' => $item->content,
                    'views' => $item->views
                ];
            });
    }

    public function getUserEngagementStats(int $userId, array $filters = []): array
    {
        $query = ContentUserView::where('user_id', $userId);

        if (isset($filters['date_from'])) {
            $query->where('viewed_at', '>=', Carbon::parse($filters['date_from']));
        }

        if (isset($filters['date_to'])) {
            $query->where('viewed_at', '<=', Carbon::parse($filters['date_to']));
        }

        $views = $query->get();

        return [
            'total_views' => $views->count(),
            'unique_content' => $views->groupBy('content_id')->count(),
            'average_duration' => $views->avg('duration_seconds'),
            'preferred_device' => $views->groupBy('device_type')->sortDesc()->keys()->first(),
            'view_trends' => $this->calculateViewTrends($views),
            'content_categories' => $this->getUserContentCategories($views)
        ];
    }

    protected function getUserContentCategories(Collection $views): array
    {
        return $views->load('content.categories')
            ->flatMap(function ($view) {
                return $view->content->categories ?? [];
            })
            ->groupBy('id')
            ->map(function ($group) {
                return [
                    'category' => $group->first(),
                    'views' => $group->count()
                ];
            })
            ->sortDesc()
            ->values()
            ->toArray();
    }

    public function createAnalyticsExport(
        string $type,
        array $filters,
        int $userId,
        ?string $format = 'csv'
    ): AnalyticsExport {
        return AnalyticsExport::create([
            'type' => $type,
            'filters' => $filters,
            'status' => 'pending',
            'format' => $format,
            'user_id' => $userId,
            'file_path' => null
        ]);
    }

    public function getExportStatus(int $exportId): ?AnalyticsExport
    {
        return AnalyticsExport::find($exportId);
    }

    public function getAvailableExportTypes(): array
    {
        return [
            'content_views' => 'Content Views',
            'user_engagement' => 'User Engagement',
            'popular_content' => 'Popular Content',
            'content_completion' => 'Content Completion Rates',
            'version_comparison' => 'Version Comparison Analytics'
        ];
    }

    public function getExportFormats(): array
    {
        return [
            'csv' => 'CSV',
            'json' => 'JSON',
            'xlsx' => 'Excel',
            'pdf' => 'PDF'
        ];
    }

    public function getContentPerformanceComparison(array $contentIds, array $filters = []): array
    {
        $results = [];

        foreach ($contentIds as $contentId) {
            $content = Content::find($contentId);
            if ($content) {
                $results[] = [
                    'content' => $content,
                    'stats' => $this->getContentEngagementStats($content, $filters)
                ];
            }
        }

        return $results;
    }

    public function getContentRetentionStats(Content $content, array $filters = []): array
    {
        $views = $this->getContentViews($content, $filters);

        $retentionPoints = [0.1, 0.25, 0.5, 0.75, 0.9, 1.0];
        $retentionData = [];

        foreach ($retentionPoints as $point) {
            $targetDuration = $content->duration_seconds * $point;
            $count = $views->filter(function ($view) use ($targetDuration) {
                return $view->duration_seconds >= $targetDuration;
            })->count();

            $retentionData[] = [
                'percentage' => $point * 100,
                'duration' => $targetDuration,
                'viewers' => $count,
                'retention_rate' => $views->isEmpty() ? 0 : ($count / $views->count()) * 100
            ];
        }

        return [
            'content_duration' => $content->duration_seconds,
            'retention_points' => $retentionData,
            'average_retention' => $views->avg('duration_seconds') / $content->duration_seconds * 100
        ];
    }

    public function getContentHeatmapData(Content $content, array $filters = []): array
    {
        $views = $this->getContentViews($content, $filters);

        if (!$content->duration_seconds) {
            return [];
        }

        $heatmap = [];
        $segmentSize = max(1, floor($content->duration_seconds / 10));

        for ($i = 0; $i < 10; $i++) {
            $start = $i * $segmentSize;
            $end = ($i + 1) * $segmentSize;
            if ($i === 9) {
                $end = $content->duration_seconds;
            }

            $count = $views->filter(function ($view) use ($start, $end) {
                return $view->duration_seconds >= $start && 
                       ($view->duration_seconds < $end || $i === 9);
            })->count();

            $heatmap[] = [
                'segment' => $i + 1,
                'start' => $start,
                'end' => $end,
                'viewers' => $count,
                'percentage' => $views->isEmpty() ? 0 : ($count / $views->count()) * 100
            ];
        }

        return $heatmap;
    }

    public function getContentCorrelations(array $contentIds, array $filters = []): array
    {
        $userViews = ContentUserView::whereIn('content_id', $contentIds)
            ->when(isset($filters['date_from']), function ($query) use ($filters) {
                $query->where('viewed_at', '>=', Carbon::parse($filters['date_from']));
            })
            ->when(isset($filters['date_to']), function ($query) use ($filters) {
                $query->where('viewed_at', '<=', Carbon::parse($filters['date_to']));
            })
            ->select('user_id', 'content_id')
            ->get()
            ->groupBy('user_id');

        $correlations = [];
        $contentPairs = $this->getAllPairs($contentIds);

        foreach ($contentPairs as $pair) {
            $contentA = $pair[0];
            $contentB = $pair[1];

            $bothViewed = 0;
            $totalUsers = 0;

            foreach ($userViews as $userId => $views) {
                $hasA = $views->contains('content_id', $contentA);
                $hasB = $views->contains('content_id', $contentB);

                if ($hasA || $hasB) {
                    $totalUsers++;
                    if ($hasA && $hasB) {
                        $bothViewed++;
                    }
                }
            }

            $correlations[] = [
                'content_a' => Content::find($contentA),
                'content_b' => Content::find($contentB),
                'both_viewed' => $bothViewed,
                'total_users' => $totalUsers,
                'correlation' => $totalUsers > 0 ? ($bothViewed / $totalUsers) * 100 : 0
            ];
        }

        return $correlations;
    }

    protected function getAllPairs(array $items): array
    {
        $pairs = [];
        $count = count($items);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $pairs[] = [$items[$i], $items[$j]];
            }
        }

        return $pairs;
    }

    public function getContentFunnelStats(array $contentIds, array $filters = []): array
    {
        $userFlows = ContentUserView::whereIn('content_id', $contentIds)
            ->when(isset($filters['date_from']), function ($query) use ($filters) {
                $query->where('viewed_at', '>=', Carbon::parse($filters['date_from']));
            })
            ->when(isset($filters['date_to']), function ($query) use ($filters) {
                $query->where('viewed_at', '<=', Carbon::parse($filters['date_to']));
            })
            ->orderBy('user_id')
            ->orderBy('viewed_at')
            ->get()
            ->groupBy('user_id');

        $funnel = [];
        $contentOrder = array_flip($contentIds);

        foreach ($userFlows as $userId => $views) {
            $sortedViews = $views->sortBy('viewed_at')->values();
            $path = [];

            foreach ($sortedViews as $view) {
                $contentId = $view->content_id;
                if (!in_array($contentId, $path)) {
                    $path[] = $contentId;
                }
            }

            // Only count paths that follow the expected order
            $isValidPath = true;
            for ($i = 1; $i < count($path); $i++) {
                if ($contentOrder[$path[$i]] < $contentOrder[$path[$i - 1]]) {
                    $isValidPath = false;
                    break;
                }
            }

            if ($isValidPath && !empty($path)) {
                $pathKey = implode('->', $path);
                if (!isset($funnel[$pathKey])) {
                    $funnel[$pathKey] = 0;
                }
                $funnel[$pathKey]++;
            }
        }

        arsort($funnel);

        return [
            'total_users' => count($userFlows),
            'common_paths' => $funnel,
            'drop_off_points' => $this->calculateDropOffPoints($contentIds, $userFlows)
        ];
    }

    protected function calculateDropOffPoints(array $contentIds, Collection $userFlows): array
    {
        $dropOffs = array_fill_keys($contentIds, 0);
        $total = 0;

        foreach ($userFlows as $userId => $views) {
            $viewedIds = $views->pluck('content_id')->unique()->toArray();
            $lastIndex = -1;

            foreach ($contentIds as $index => $contentId) {
                if (in_array($contentId, $viewedIds)) {
                    $lastIndex = $index;
                } else {
                    break;
                }
            }

            if ($lastIndex >= 0 && $lastIndex < count($contentIds) - 1) {
                $dropOffs[$contentIds[$lastIndex]]++;
                $total++;
            }
        }

        return array_map(function ($count) use ($total) {
            return $total > 0 ? ($count / $total) * 100 : 0;
        }, $dropOffs);
    }
}