<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use App\Models\Content;
use Illuminate\Support\Collection;

class ContentSuggestionService
{
    public function getSuggestions(Content $content): Collection
    {
        // Get content context
        $context = $this->getContentContext($content);

        // Generate AI suggestions
        $aiSuggestions = $this->generateAiSuggestions($context);

        // Combine with system suggestions
        $suggestions = $this->getSystemSuggestions($content)
            ->merge($aiSuggestions);

        // Rank and return suggestions
        return $this->rankSuggestions($suggestions);
    }

    protected function getContentContext(Content $content): string
    {
        return "Content Title: {$content->title}\n"
            . "Content Type: {$content->type}\n"
            . "Content Body: {$content->body}\n"
            . "Category: {$content->category->name}\n";
    }

    protected function generateAiSuggestions(string $context): Collection
    {
        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a content suggestion assistant. Provide 3-5 relevant content suggestions based on the given context.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $context
                    ]
                ],
                'temperature' => 0.7,
            ]);

            return collect(json_decode($response->choices[0]->message->content, true));
        } catch (\Exception $e) {
            return collect();
        }
    }

    protected function getSystemSuggestions(Content $content): Collection
    {
        // Get related content suggestions
        $related = $content->category->contents()
            ->where('id', '!=', $content->id)
            ->latest()
            ->take(3)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => "Related: {$item->title}",
                    'type' => 'related_content',
                    'score' => 70 // Base score for related content
                ];
            });

        // Get trending content suggestions
        $trending = Content::trending()
            ->take(2)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => "Trending: {$item->title}",
                    'type' => 'trending',
                    'score' => 80 // Base score for trending content
                ];
            });

        return $related->merge($trending);
    }

    protected function rankSuggestions(Collection $suggestions): Collection
    {
        return $suggestions
            ->sortByDesc('score')
            ->values();
    }

    public function getContextAwareSuggestions(int $contentId, array $context): Collection
    {
        $content = Content::findOrFail($contentId);
        $fullContext = $this->getContentContext($content) . "\nAdditional Context: " . json_encode($context);

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a context-aware content suggestion assistant. Provide highly relevant suggestions based on the given content and additional context.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $fullContext
                    ]
                ],
                'temperature' => 0.5,
            ]);

            return collect(json_decode($response->choices[0]->message->content, true))
                ->map(function ($suggestion) {
                    return array_merge($suggestion, [
                        'type' => 'context_aware',
                        'score' => $suggestion['score'] ?? 85
                    ]);
                });
        } catch (\Exception $e) {
            return collect();
        }
    }

    public function savePreferences(int $userId, array $preferences): void
    {
        $user = User::findOrFail($userId);
        
        foreach ($preferences as $pref) {
            $user->preferences()->updateOrCreate(
                ['type' => $pref['type']],
                ['value' => $pref['value']]
            );
        }
    }

    public function rankSuggestions(array $suggestions): array
    {
        // Apply personalization weights based on user preferences
        $personalized = array_map(function ($suggestion) {
            $personalizationScore = $this->calculatePersonalizationScore($suggestion);
            return array_merge($suggestion, [
                'score' => $suggestion['score'] * $personalizationScore
            ]);
        }, $suggestions);

        // Sort by final score
        usort($personalized, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $personalized;
    }

    protected function calculatePersonalizationScore(array $suggestion): float
    {
        // Default weight if no preferences exist
        $typeWeight = 1.0;
        
        // Get weights from user preferences if available
        $pref = auth()->user()->preferences()->where('type', $suggestion['type'])->first();
        if ($pref) {
            $typeWeight = max(0.5, min(1.5, (float)$pref->value));
        }

        return $typeWeight;
    }
}