<?php

namespace Includes\Services;

use Includes\Services\DatabaseConnection;
use Includes\Services\OpenAIService;

class PersonalizationService
{
    protected $db;
    protected $aiService;
    protected $cache;
    protected $logger;

    public function __construct(
        DatabaseConnection $db,
        OpenAIService $aiService,
        CacheInterface $cache = null,
        LoggerInterface $logger = null
    ) {
        $this->db = $db;
        $this->aiService = $aiService;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    public function trackEvent(array $eventData): bool
    {
        return $this->db->table('personalization_events')->insert([
            'event_type' => $eventData['event_type'],
            'user_id' => $eventData['user_id'] ?? null,
            'content_id' => $eventData['content_id'] ?? null,
            'session_id' => $eventData['session_id'],
            'rule_id' => $eventData['rule_id'] ?? null,
            'properties' => json_encode($eventData['properties'] ?? []),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getPersonalizedContent(int $userId, string $context): array
    {
        // Get user behavior patterns
        $behavior = $this->analyzeUserBehavior($userId);
        
        // Generate AI-powered recommendations
        $prompt = $this->createRecommendationPrompt($behavior, $context);
        $recommendations = $this->aiService->generateRecommendations($prompt);
        
        return [
            'behavior' => $behavior,
            'recommendations' => $recommendations
        ];
    }

    protected function analyzeUserBehavior(int $userId): array
    {
        $events = $this->db->table('personalization_events')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return $this->processBehaviorPatterns($events);
    }

    protected function processBehaviorPatterns(array $events): array
    {
        // Basic pattern analysis
        $patterns = [
            'content_types' => [],
            'time_of_day' => [],
            'dwell_times' => []
        ];

        foreach ($events as $event) {
            $props = json_decode($event->properties, true);
            
            // Track content type preferences
            if (isset($props['content_type'])) {
                $patterns['content_types'][$props['content_type']] = 
                    ($patterns['content_types'][$props['content_type']] ?? 0) + 1;
            }
            
            // Track time patterns
            $hour = date('H', strtotime($event->created_at));
            $patterns['time_of_day'][$hour] = ($patterns['time_of_day'][$hour] ?? 0) + 1;
            
            // Track engagement
            if (isset($props['dwell_time'])) {
                $patterns['dwell_times'][] = $props['dwell_time'];
            }
        }

        return $patterns;
    }

    protected function createRecommendationPrompt(array $behavior, string $context): string
    {
        $prompt = "User Behavior Analysis:\n";
        
        // Analyze top content types if available
        if (!empty($behavior['content_types'])) {
            arsort($behavior['content_types']);
            $topTypes = array_slice($behavior['content_types'], 0, 3);
            $prompt .= "- Most engaged content types: " . implode(', ', array_keys($topTypes)) . "\n";
        }

        // Analyze peak activity hours if available
        if (!empty($behavior['time_of_day'])) {
            arsort($behavior['time_of_day']);
            $peakHours = array_slice($behavior['time_of_day'], 0, 3);
            $prompt .= "- Peak activity hours: " . implode(', ', array_keys($peakHours)) . "\n";
        }

        // Calculate average dwell time if available
        if (!empty($behavior['dwell_times'])) {
            $avgDwell = array_sum($behavior['dwell_times']) / count($behavior['dwell_times']);
            $prompt .= "- Average engagement time: " . round($avgDwell) . " seconds\n\n";
        } else {
            $prompt .= "\n";
        }

        $prompt .= "Current Context: $context\n\n";
        $prompt .= "Generate 5 personalized content recommendations that would be most relevant to this user, ";
        $prompt .= "considering their content preferences and activity patterns. Include:\n";
        $prompt .= "1. Title\n2. Content type\n3. Suggested length\n4. Ideal publish time\n";
        $prompt .= "5. Brief justification based on their behavior patterns";

        return $prompt;
    }
}
