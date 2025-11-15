<?php
declare(strict_types=1);

class ContentScorer
{
    private const TAG_WEIGHT = 0.6;
    private const TYPE_WEIGHT = 0.3;
    private const POPULARITY_WEIGHT = 0.1;

    public function calculateScore(array $content, array $userProfile): float
    {
        $tagScore = $this->calculateTagMatchScore(
            $content['tags'] ?? [],
            $userProfile['interests'] ?? []
        );

        $typeScore = $this->calculateTypeMatchScore(
            $content['type'] ?? 'article',
            $userProfile['preferred_content_types'] ?? []
        );

        $popularityScore = $this->calculatePopularityScore(
            $content['views'] ?? 0,
            $content['engagement_rate'] ?? 0
        );

        return ($tagScore * self::TAG_WEIGHT) + 
               ($typeScore * self::TYPE_WEIGHT) + 
               ($popularityScore * self::POPULARITY_WEIGHT);
    }

    private function calculateTagMatchScore(array $contentTags, array $userInterests): float
    {
        $matchingTags = array_intersect($contentTags, $userInterests);
        return count($matchingTags) / max(1, count($contentTags));
    }

    private function calculateTypeMatchScore(string $contentType, array $preferredTypes): float
    {
        return in_array($contentType, $preferredTypes) ? 1.0 : 0.5;
    }

    private function calculatePopularityScore(int $views, float $engagementRate): float
    {
        $normalizedViews = min($views / 1000, 1.0);
        $normalizedEngagement = min($engagementRate / 0.5, 1.0);
        return ($normalizedViews + $normalizedEngagement) / 2;
    }
}
