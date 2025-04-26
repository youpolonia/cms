<?php

namespace App\Services;

use App\Models\ModerationQueue;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContentModerationService
{
    protected $openAiKey;
    protected $moderationThreshold = 0.7;

    public function __construct()
    {
        $this->openAiKey = config('services.openai.key');
    }

    public function analyzeContent(string $content, string $contentType, User $user): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openAiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/moderations', [
                'input' => $content,
            ]);

            $result = $response->json();

            if (!isset($result['results'][0])) {
                throw new \Exception('Invalid moderation API response');
            }

            $moderationResult = $result['results'][0];
            $flags = $moderationResult['categories'];
            $scores = $moderationResult['category_scores'];
            $flagged = $moderationResult['flagged'];

            $analysis = [
                'flags' => $flags,
                'scores' => $scores,
                'flagged' => $flagged,
                'suggestions' => $this->generateSuggestions($flags, $scores),
            ];

            if ($flagged) {
                $this->addToModerationQueue($content, $contentType, $user, $analysis);
            }

            return $analysis;
        } catch (\Exception $e) {
            Log::error('Content moderation failed: ' . $e->getMessage());
            return [
                'error' => 'Moderation service unavailable',
                'flagged' => false,
            ];
        }
    }

    protected function generateSuggestions(array $flags, array $scores): array
    {
        $suggestions = [];
        
        foreach ($flags as $category => $flagged) {
            if ($flagged && $scores[$category] > $this->moderationThreshold) {
                $suggestions[] = $this->getSuggestionForCategory($category);
            }
        }

        return $suggestions;
    }

    protected function getSuggestionForCategory(string $category): string
    {
        $suggestions = [
            'hate' => 'Content contains potentially hateful language',
            'hate/threatening' => 'Content contains threatening language',
            'self-harm' => 'Content discusses self-harm',
            'sexual' => 'Content contains sexual references',
            'sexual/minors' => 'Content contains references to minors in sexual context',
            'violence' => 'Content contains violent language',
            'violence/graphic' => 'Content contains graphic violence',
        ];

        return $suggestions[$category] ?? 'Content may violate guidelines';
    }

    protected function addToModerationQueue(
        string $content, 
        string $contentType, 
        User $user,
        array $analysis
    ): ModerationQueue {
        return ModerationQueue::create([
            'content_type' => $contentType,
            'content_id' => null, // Will be set when content is saved
            'reported_by' => $user->id,
            'status' => 'pending',
            'moderation_metadata' => $analysis,
            'priority' => $this->calculatePriority($analysis),
        ]);
    }

    protected function calculatePriority(array $analysis): int
    {
        $priority = 1;
        
        foreach ($analysis['scores'] as $score) {
            if ($score > 0.9) $priority = 10;
            elseif ($score > 0.8 && $priority < 8) $priority = 8;
            elseif ($score > 0.7 && $priority < 5) $priority = 5;
        }

        return $priority;
    }
}