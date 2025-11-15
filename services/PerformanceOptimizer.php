<?php
declare(strict_types=1);

namespace CMS\Services;

use CMS\Includes\Models\CacheModel;
use CMS\Includes\Models\ContentModel;
use CMS\Includes\Models\PerformanceModel;

class PerformanceOptimizer {
    private CacheModel $cacheModel;
    private ContentModel $contentModel;
    private PerformanceModel $performanceModel;

    public function __construct(
        CacheModel $cacheModel,
        ContentModel $contentModel,
        PerformanceModel $performanceModel
    ) {
        $this->cacheModel = $cacheModel;
        $this->contentModel = $contentModel;
        $this->performanceModel = $performanceModel;
    }

    /**
     * Cache personalized content for faster delivery
     */
    public function cachePersonalizedContent(int $userId, array $content): bool {
        $cacheKey = "user_{$userId}_personalized";
        return $this->cacheModel->set($cacheKey, $content, 3600); // 1 hour TTL
    }

    /**
     * Prefetch recommendations for likely next requests
     */
    public function prefetchRecommendations(int $userId): void {
        $likelyContent = $this->performanceModel->getLikelyNextContent($userId);
        foreach ($likelyContent as $contentId) {
            $this->contentModel->prefetch($contentId);
        }
    }

    /**
     * Optimize content delivery based on user patterns
     */
    public function optimizeDelivery(int $userId): array {
        $optimizations = [];
        
        // Compression preferences
        $optimizations['compression'] = $this->performanceModel->getCompressionPreference($userId);
        
        // Delivery method
        $optimizations['delivery_method'] = $this->performanceModel->getOptimalDeliveryMethod($userId);
        
        // Resource prioritization
        $optimizations['resource_priority'] = $this->performanceModel->getResourcePriority($userId);

        return $optimizations;
    }

    /**
     * Monitor and log performance metrics
     */
    public function monitorPerformance(array $metrics): bool {
        return $this->performanceModel->logMetrics($metrics);
    }

    /**
     * Get performance optimization recommendations
     */
    public function getOptimizationRecommendations(): array {
        return $this->performanceModel->getRecommendations();
    }
}
