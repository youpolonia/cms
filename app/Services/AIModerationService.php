<?php

namespace App\Services;

use App\Models\Content;
use App\Models\ModerationQueue;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AIModerationService
{
    protected OpenAIService $openAIService;
    protected ContentModerationService $moderationService;

    public function __construct(
        OpenAIService $openAIService,
        ContentModerationService $moderationService
    ) {
        $this->openAIService = $openAIService;
        $this->moderationService = $moderationService;
    }

    public function analyzeContent(Content $content): array
    {
        $textContent = $this->extractTextContent($content);
        
        // Basic moderation check
        $moderationResult = $this->openAIService->moderateContent($textContent);
        
        // Detailed content analysis
        $analysisResult = $this->getContentAnalysis($textContent);
        
        return [
            'moderation' => $moderationResult,
            'analysis' => $analysisResult,
            'risk_score' => $this->calculateRiskScore($moderationResult, $analysisResult),
            'suggested_actions' => $this->getSuggestedActions($moderationResult, $analysisResult)
        ];
    }

    public function getModerationSuggestions(ModerationQueue $queueItem): array
    {
        $content = $queueItem->content;
        $analysis = $this->analyzeContent($content);
        
        return [
            'decision_suggestions' => $this->getDecisionSuggestions($analysis),
            'reason_suggestions' => $this->getReasonSuggestions($analysis),
            'action_suggestions' => $this->getActionSuggestions($analysis),
            'priority_score' => $this->calculatePriorityScore($analysis)
        ];
    }

    protected function extractTextContent(Content $content): string
    {
        // Extract text from content based on type
        return $content->content_text ?? '';
    }

    protected function getContentAnalysis(string $content): array
    {
        try {
            $response = $this->openAIService->generateContent([
                'type' => 'analyze',
                'prompt' => "Analyze this content for moderation purposes:\n$content",
                'response_format' => 'json_object',
                'model' => 'gpt-4',
                'validation_rules' => [
                    'Identify potential policy violations',
                    'Detect harmful or sensitive content',
                    'Assess content quality',
                    'Flag controversial topics'
                ]
            ]);

            return json_decode($response['content'], true) ?? [];
        } catch (\Exception $e) {
            Log::error('AI content analysis failed: ' . $e->getMessage());
            return [];
        }
    }

    protected function calculateRiskScore(array $moderation, array $analysis): float
    {
        $baseScore = $moderation['flagged'] ? 0.7 : 0.3;
        
        // Adjust based on category scores
        $categoryAdjustment = array_sum($moderation['scores']) / count($moderation['scores']);
        
        // Apply analysis factors
        $analysisFactor = isset($analysis['risk_level']) 
            ? $analysis['risk_level'] / 10 
            : 0.5;
            
        return min(1.0, $baseScore * 0.6 + $categoryAdjustment * 0.2 + $analysisFactor * 0.2);
    }

    protected function getSuggestedActions(array $moderation, array $analysis): array
    {
        $actions = [];
        
        if ($moderation['flagged']) {
            $actions[] = [
                'action' => 'review',
                'priority' => 'high',
                'reason' => 'Content flagged by AI moderation'
            ];
        }
        
        if (isset($analysis['sensitive_topics']) && !empty($analysis['sensitive_topics'])) {
            $actions[] = [
                'action' => 'review',
                'priority' => 'medium',
                'reason' => 'Contains sensitive topics: ' . implode(', ', $analysis['sensitive_topics'])
            ];
        }
        
        return $actions;
    }

    protected function getDecisionSuggestions(array $analysis): array
    {
        if ($analysis['risk_score'] > 0.8) {
            return ['reject' => 0.9, 'approve' => 0.1];
        }
        
        if ($analysis['risk_score'] > 0.5) {
            return ['reject' => 0.7, 'approve' => 0.3];
        }
        
        return ['approve' => 0.8, 'reject' => 0.2];
    }

    protected function getReasonSuggestions(array $analysis): array
    {
        $reasons = [];
        
        foreach ($analysis['moderation']['categories'] as $category => $flagged) {
            if ($flagged) {
                $reasons[$category] = $analysis['moderation']['scores'][$category];
            }
        }
        
        arsort($reasons);
        return array_slice($reasons, 0, 3, true);
    }

    protected function getActionSuggestions(array $analysis): array
    {
        $actions = $this->moderationService->getModerationActionOptions();
        $suggested = [];
        
        if ($analysis['risk_score'] > 0.7) {
            $suggested['hide_content'] = 0.9;
            $suggested['disable_comments'] = 0.8;
        }
        
        if (isset($analysis['analysis']['sensitive_content'])) {
            $suggested['add_content_warning'] = 0.7;
        }
        
        return $suggested;
    }

    protected function calculatePriorityScore(array $analysis): int
    {
        $score = (int) ($analysis['risk_score'] * 100);
        
        // Increase priority for certain content types
        if (isset($analysis['analysis']['content_type'])) {
            if ($analysis['analysis']['content_type'] === 'public_post') {
                $score += 20;
            }
        }
        
        return min(100, $score);
    }

    public function shouldAutoModerate(Content $content): bool
    {
        $analysis = $this->analyzeContent($content);
        return $analysis['risk_score'] < 0.3 || $analysis['risk_score'] > 0.9;
    }

    public function autoModerate(Content $content): ?ModerationQueue
    {
        $analysis = $this->analyzeContent($content);
        
        if ($analysis['risk_score'] < 0.3) {
            return $this->moderationService->moderateContent(
                $content,
                User::where('is_system', true)->first(),
                'approved',
                'Automatically approved by AI - low risk score'
            );
        }
        
        if ($analysis['risk_score'] > 0.9) {
            return $this->moderationService->moderateContent(
                $content,
                User::where('is_system', true)->first(),
                'rejected',
                'Automatically rejected by AI - high risk score'
            );
        }
        
        return null;
    }
}