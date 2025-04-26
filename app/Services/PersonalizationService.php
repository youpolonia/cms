<?php

namespace App\Services;

use App\Models\User;
use App\Models\Content;
use App\Models\ContentUserView;
use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PersonalizationService
{
    protected $user;
    protected $cacheTtl = 3600; // 1 hour

    public function __construct(?User $user = null)
    {
        $this->user = $user;
    }

    public function getPersonalizedContent(int $limit = 10): Collection
    {
        if (!$this->user) {
            return $this->getTrendingContent($limit);
        }

        return Cache::remember(
            "user.{$this->user->id}.personalized_content",
            $this->cacheTtl,
            function () use ($limit) {
                return $this->calculatePersonalizedContent($limit);
            }
        );
    }

    protected function calculatePersonalizedContent(int $limit): Collection
    {
        $preferredCategories = $this->getUserPreferredCategories();
        $viewedContent = $this->getUserViewedContentIds();
        $similarUsersContent = $this->getSimilarUsersContent();

        $query = Content::query()
            ->whereNotIn('id', $viewedContent)
            ->with(['currentVersion', 'category'])
            ->limit($limit * 2); // Get extra to account for filtering

        // Prioritize preferred categories
        if ($preferredCategories->isNotEmpty()) {
            $query->orderByRaw(
                'FIELD(category_id, ' . $preferredCategories->implode(',') . ') DESC'
            );
        }

        // Add similar users' content
        if ($similarUsersContent->isNotEmpty()) {
            $query->orWhereIn('id', $similarUsersContent);
        }

        return $query->get()
            ->shuffle()
            ->take($limit);
    }

    public function getUserPreferredCategories(): Collection
    {
        if (!$this->user) {
            return collect();
        }

        return Cache::remember(
            "user.{$this->user->id}.preferred_categories",
            $this->cacheTtl,
            function () {
                return ContentUserView::where('user_id', $this->user->id)
                    ->join('contents', 'content_user_views.content_id', '=', 'contents.id')
                    ->select('contents.category_id', DB::raw('COUNT(*) as view_count'))
                    ->groupBy('contents.category_id')
                    ->orderBy('view_count', 'desc')
                    ->limit(5)
                    ->pluck('category_id');
            }
        );
    }

    protected function getUserViewedContentIds(): Collection
    {
        if (!$this->user) {
            return collect();
        }

        return ContentUserView::where('user_id', $this->user->id)
            ->pluck('content_id');
    }

    protected function getSimilarUsersContent(): Collection
    {
        if (!$this->user) {
            return collect();
        }

        $similarUserIds = $this->getSimilarUserIds();

        if ($similarUserIds->isEmpty()) {
            return collect();
        }

        return ContentUserView::whereIn('user_id', $similarUserIds)
            ->select('content_id', DB::raw('COUNT(*) as view_count'))
            ->groupBy('content_id')
            ->orderBy('view_count', 'desc')
            ->limit(20)
            ->pluck('content_id');
    }

    protected function getSimilarUserIds(): Collection
    {
        $userCategories = $this->getUserPreferredCategories();

        if ($userCategories->isEmpty()) {
            return collect();
        }

        return ContentUserView::join('contents', 'content_user_views.content_id', '=', 'contents.id')
            ->whereIn('contents.category_id', $userCategories)
            ->select('content_user_views.user_id', DB::raw('COUNT(*) as match_count'))
            ->where('user_id', '!=', $this->user->id)
            ->groupBy('user_id')
            ->orderBy('match_count', 'desc')
            ->limit(10)
            ->pluck('user_id');
    }

    public function getTrendingContent(int $limit = 10): Collection
    {
        return Cache::remember(
            'trending_content',
            $this->cacheTtl / 2, // Shorter cache for trending content
            function () use ($limit) {
                return ContentUserView::select('content_id', DB::raw('COUNT(*) as views'))
                    ->where('created_at', '>', now()->subDays(7))
                    ->groupBy('content_id')
                    ->orderBy('views', 'desc')
                    ->limit($limit)
                    ->with(['content.currentVersion', 'content.category'])
                    ->get()
                    ->pluck('content');
            }
        );
    }

    public function getRecommendedForYou(int $limit = 5): Collection
    {
        if (!$this->user) {
            return collect();
        }

        return $this->getPersonalizedContent($limit);
    }

    public function getContinueReading(int $limit = 3): Collection
    {
        if (!$this->user) {
            return collect();
        }

        return Cache::remember(
            "user.{$this->user->id}.continue_reading",
            $this->cacheTtl,
            function () use ($limit) {
                return ContentUserView::where('user_id', $this->user->id)
                    ->where('view_duration', '<', DB::raw('content_duration * 0.9')) // Didn't finish
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->with(['content.currentVersion'])
                    ->get()
                    ->pluck('content');
            }
        );
    }

    public function getPopularInCategory(Category $category, int $limit = 5): Collection
    {
        return Cache::remember(
            "category.{$category->id}.popular",
            $this->cacheTtl,
            function () use ($category, $limit) {
                return Content::where('category_id', $category->id)
                    ->with(['currentVersion'])
                    ->orderBy('view_count', 'desc')
                    ->limit($limit)
                    ->get();
            }
        );
    }

    public function refreshUserRecommendations(User $user): void
    {
        Cache::forget("user.{$user->id}.personalized_content");
        Cache::forget("user.{$user->id}.preferred_categories");
        Cache::forget("user.{$user->id}.continue_reading");
    }

    public function recordUserInteraction(User $user, Content $content, string $interactionType): void
    {
        // Update real-time preferences
        $this->refreshUserRecommendations($user);

        // Additional tracking could be added here
        // For example: increment preference scores for this content's category
    }

    public function getContentRecommendations(Content $content, int $limit = 5): Collection
    {
        return Cache::remember(
            "content.{$content->id}.recommendations",
            $this->cacheTtl,
            function () use ($content, $limit) {
                // Get content with same category
                $sameCategory = Content::where('category_id', $content->category_id)
                    ->where('id', '!=', $content->id)
                    ->orderBy('view_count', 'desc')
                    ->limit($limit)
                    ->with(['currentVersion'])
                    ->get();

                // Get content often viewed together
                $viewedTogether = ContentUserView::whereIn('user_id', function($query) use ($content) {
                        $query->select('user_id')
                            ->from('content_user_views')
                            ->where('content_id', $content->id);
                    })
                    ->select('content_id', DB::raw('COUNT(*) as view_count'))
                    ->where('content_id', '!=', $content->id)
                    ->groupBy('content_id')
                    ->orderBy('view_count', 'desc')
                    ->limit($limit)
                    ->pluck('content_id');

                $viewedTogetherContent = Content::whereIn('id', $viewedTogether)
                    ->with(['currentVersion'])
                    ->get();

                return $sameCategory->merge($viewedTogetherContent)
                    ->unique('id')
                    ->shuffle()
                    ->take($limit);
            }
        );
    }

    public function getPersonalizationSettings(User $user): array
    {
        return [
            'preferred_categories' => $this->getUserPreferredCategories(),
            'content_preferences' => $this->getContentPreferences($user),
            'recommendation_weights' => $this->getRecommendationWeights()
        ];
    }

    protected function getContentPreferences(User $user): array
    {
        // Could be expanded to include more detailed preferences
        return [
            'average_view_duration' => ContentUserView::where('user_id', $user->id)
                ->avg('view_duration'),
            'preferred_content_length' => Content::join('content_user_views', 'contents.id', '=', 'content_user_views.content_id')
                ->where('user_id', $user->id)
                ->selectRaw('AVG(LENGTH(content_versions.content)) as avg_length')
                ->join('content_versions', 'contents.current_version_id', '=', 'content_versions.id')
                ->value('avg_length')
        ];
    }

    protected function getRecommendationWeights(): array
    {
        return [
            'category_preference' => 0.6,
            'similar_users' => 0.3,
            'trending' => 0.1
        ];
    }
}