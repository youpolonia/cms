<?php

namespace App\Services;

use App\Models\Content;
use App\Models\User;
use App\Models\ContentUserView;
use App\Models\Media;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class ContentPersonalizationService
{
    protected $user;
    protected $content;
    protected $cacheTtl = 3600; // 1 hour

    public function __construct(?User $user = null)
    {
        $this->user = $user;
    }

    public function forUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function forContent(Content $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getPersonalizedContent(int $limit = 10): Collection
    {
        if (!$this->user) {
            return $this->getTrendingContent($limit);
        }

        $cacheKey = "user:{$this->user->id}:personalized_content";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($limit) {
            // Combine different recommendation strategies with weights
            $recommendations = collect();

            // Content based on user's interests (30% weight)
            $interestBased = $this->getInterestBasedRecommendations($limit * 0.3);
            $recommendations = $recommendations->merge($interestBased);

            // Content based on user's viewing history (40% weight)
            $historyBased = $this->getHistoryBasedRecommendations($limit * 0.4);
            $recommendations = $recommendations->merge($historyBased);

            // Content based on similar users (20% weight)
            $collaborativeBased = $this->getCollaborativeRecommendations($limit * 0.2);
            $recommendations = $recommendations->merge($collaborativeBased);

            // Trending content (10% weight)
            $trending = $this->getTrendingContent($limit * 0.1);
            $recommendations = $recommendations->merge($trending);

            // Remove duplicates and already viewed content
            $recommendations = $recommendations->unique('id')
                ->reject(function ($content) {
                    return $this->user->hasViewedContent($content);
                });

            // Sort by recommendation score and take the limit
            return $recommendations->sortByDesc('score')
                ->take($limit);
        });
    }

    protected function getInterestBasedRecommendations(int $limit): Collection
    {
        if (!$this->user->interests->count()) {
            return collect();
        }

        return Content::query()
            ->with(['categories', 'type'])
            ->whereHas('categories', function($query) {
                $query->whereIn('id', $this->user->interests->pluck('id'));
            })
            ->where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->take($limit * 2) // Get more to account for filtering
            ->get()
            ->map(function ($content) {
                $content->score = $this->calculateInterestScore($content);
                return $content;
            })
            ->sortByDesc('score')
            ->take($limit);
    }

    protected function calculateInterestScore(Content $content): float
    {
        $matchingInterests = $content->categories->pluck('id')
            ->intersect($this->user->interests->pluck('id'))
            ->count();

        $totalInterests = $this->user->interests->count();

        return $matchingInterests / max(1, $totalInterests);
    }

    protected function getHistoryBasedRecommendations(int $limit): Collection
    {
        $viewedContent = $this->user->contentViews()
            ->with('content.categories')
            ->orderBy('viewed_at', 'desc')
            ->take(10)
            ->get()
            ->pluck('content');

        if ($viewedContent->isEmpty()) {
            return collect();
        }

        // Get content from same categories
        $categoryIds = $viewedContent->flatMap->categories->pluck('id')->unique();

        return Content::query()
            ->with(['categories', 'type'])
            ->whereHas('categories', function($query) use ($categoryIds) {
                $query->whereIn('id', $categoryIds);
            })
            ->where('status', 'published')
            ->whereNotIn('id', $viewedContent->pluck('id'))
            ->orderBy('published_at', 'desc')
            ->take($limit * 2)
            ->get()
            ->map(function ($content) use ($viewedContent) {
                $content->score = $this->calculateHistoryScore($content, $viewedContent);
                return $content;
            })
            ->sortByDesc('score')
            ->take($limit);
    }

    protected function calculateHistoryScore(Content $content, Collection $viewedContent): float
    {
        $score = 0;

        // Score based on category overlap
        $contentCategories = $content->categories->pluck('id');
        foreach ($viewedContent as $viewed) {
            $commonCategories = $viewed->categories->pluck('id')
                ->intersect($contentCategories)
                ->count();
            $score += $commonCategories / max(1, $viewed->categories->count());
        }

        // Normalize score
        return $score / max(1, $viewedContent->count());
    }

    protected function getCollaborativeRecommendations(int $limit): Collection
    {
        $similarUsers = $this->getSimilarUsers(5);

        if ($similarUsers->isEmpty()) {
            return collect();
        }

        $viewedBySimilar = ContentUserView::query()
            ->whereIn('user_id', $similarUsers->pluck('id'))
            ->with('content')
            ->whereNotIn('content_id', $this->user->contentViews()->pluck('content_id'))
            ->groupBy('content_id')
            ->selectRaw('content_id, count(*) as view_count')
            ->orderBy('view_count', 'desc')
            ->take($limit * 2)
            ->get()
            ->pluck('content');

        return $viewedBySimilar->map(function ($content) use ($similarUsers) {
                $content->score = $this->calculateCollaborativeScore($content, $similarUsers);
                return $content;
            })
            ->sortByDesc('score')
            ->take($limit);
    }

    protected function getSimilarUsers(int $limit): Collection
    {
        $cacheKey = "user:{$this->user->id}:similar_users";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($limit) {
            // Find users who viewed similar content
            $viewedContentIds = $this->user->contentViews()->pluck('content_id');

            return User::query()
                ->whereHas('contentViews', function($query) use ($viewedContentIds) {
                    $query->whereIn('content_id', $viewedContentIds);
                })
                ->withCount(['contentViews' => function($query) use ($viewedContentIds) {
                    $query->whereIn('content_id', $viewedContentIds);
                }])
                ->orderBy('content_views_count', 'desc')
                ->where('id', '!=', $this->user->id)
                ->take($limit)
                ->get();
        });
    }

    protected function calculateCollaborativeScore(Content $content, Collection $similarUsers): float
    {
        $viewCount = ContentUserView::whereIn('user_id', $similarUsers->pluck('id'))
            ->where('content_id', $content->id)
            ->count();

        return $viewCount / max(1, $similarUsers->count());
    }

    public function getTrendingContent(int $limit = 10): Collection
    {
        $cacheKey = 'trending_content';

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($limit) {
            return Content::query()
                ->with(['categories', 'type'])
                ->where('status', 'published')
                ->where('published_at', '>=', now()->subDays(7))
                ->withCount('views')
                ->orderBy('views_count', 'desc')
                ->orderBy('published_at', 'desc')
                ->take($limit)
                ->get()
                ->each(function ($content) {
                    $content->score = $content->views_count / max(1, $content->days_since_published);
                });
        });
    }

    public function getPersonalizedMediaForContent(Content $content, int $limit = 5): Collection
    {
        if (!$this->user) {
            return $content->media()->take($limit)->get();
        }

        $cacheKey = "user:{$this->user->id}:content:{$content->id}:personalized_media";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($content, $limit) {
            return Media::query()
                ->where('content_id', $content->id)
                ->with(['collection'])
                ->whereHas('collection', function($query) {
                    $query->whereIn('id', $this->user->preferredMediaCollections->pluck('id'));
                })
                ->orderBy('created_at', 'desc')
                ->take($limit)
                ->get();
        });
    }

    public function trackContentView(Content $content, ?string $ipAddress = null): void
    {
        if ($this->user) {
            ContentUserView::firstOrCreate([
                'user_id' => $this->user->id,
                'content_id' => $content->id
            ], [
                'viewed_at' => now(),
                'ip_address' => $ipAddress
            ]);
        }

        // Update content view count
        $content->increment('views_count');
    }

    public function getUserContentPreferences(): array
    {
        if (!$this->user) {
            return [];
        }

        $cacheKey = "user:{$this->user->id}:content_preferences";

        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            $views = $this->user->contentViews()
                ->with('content.categories')
                ->orderBy('viewed_at', 'desc')
                ->take(50)
                ->get();

            $categories = $views->flatMap->content->categories
                ->groupBy('id')
                ->map(function ($group) {
                    return [
                        'category' => $group->first(),
                        'count' => $group->count(),
                        'last_viewed' => $group->max('pivot.viewed_at')
                    ];
                })
                ->sortByDesc('count')
                ->take(5)
                ->pluck('category');

            $contentTypes = $views->groupBy('content.type_id')
                ->map(function ($group) {
                    return [
                        'type' => $group->first()->content->type,
                        'count' => $group->count(),
                        'last_viewed' => $group->max('viewed_at')
                    ];
                })
                ->sortByDesc('count')
                ->take(3)
                ->pluck('type');

            return [
                'preferred_categories' => $categories,
                'preferred_content_types' => $contentTypes,
                'view_history_count' => $views->count(),
                'last_viewed_at' => $views->max('viewed_at')
            ];
        });
    }

    public function refreshUserRecommendations(User $user): void
    {
        $cacheKeys = [
            "user:{$user->id}:personalized_content",
            "user:{$user->id}:similar_users",
            "user:{$user->id}:content_preferences"
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    public function getContentRecommendationFactors(Content $content): array
    {
        if (!$this->user) {
            return [];
        }

        $factors = [];

        // Interest factor
        $interestScore = $this->calculateInterestScore($content);
        if ($interestScore > 0) {
            $factors[] = [
                'type' => 'interest',
                'score' => $interestScore,
                'message' => "Matches {$this->user->interests->pluck('name')->join(', ')} interests"
            ];
        }

        // History factor
        $viewedContent = $this->user->contentViews()
            ->with('content.categories')
            ->orderBy('viewed_at', 'desc')
            ->take(10)
            ->get()
            ->pluck('content');

        if ($viewedContent->isNotEmpty()) {
            $historyScore = $this->calculateHistoryScore($content, $viewedContent);
            if ($historyScore > 0) {
                $factors[] = [
                    'type' => 'history',
                    'score' => $historyScore,
                    'message' => "Similar to previously viewed content"
                ];
            }
        }

        // Collaborative factor
        $similarUsers = $this->getSimilarUsers(5);
        if ($similarUsers->isNotEmpty()) {
            $collabScore = $this->calculateCollaborativeScore($content, $similarUsers);
            if ($collabScore > 0) {
                $factors[] = [
                    'type' => 'collaborative',
                    'score' => $collabScore,
                    'message' => "Popular with similar users"
                ];
            }
        }

        return $factors;
    }
}