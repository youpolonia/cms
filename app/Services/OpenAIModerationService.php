<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class OpenAIModerationService
{
    public function moderateContent(string $content): array
    {
        try {
            $response = OpenAI::moderations()->create([
                'input' => $content,
                'model' => 'text-moderation-latest'
            ]);

            $results = $response->results[0];
            $flagged = $results->flagged;

            return [
                'flagged' => $flagged,
                'categories' => $results->categories,
                'category_scores' => $results->category_scores,
                'moderation_id' => $response->id,
                'model' => $response->model,
                'timestamp' => now()->toDateTimeString()
            ];

        } catch (\Exception $e) {
            Log::error('OpenAI Moderation Failed: ' . $e->getMessage());
            
            return [
                'flagged' => false,
                'error' => $e->getMessage(),
                'timestamp' => now()->toDateTimeString()
            ];
        }
    }

    public function shouldFlagContent(array $moderationResults): bool
    {
        if ($moderationResults['flagged'] ?? false) {
            return true;
        }

        $highRiskCategories = ['harassment', 'hate', 'self-harm'];
        foreach ($highRiskCategories as $category) {
            if (($moderationResults['category_scores'][$category] ?? 0) > 0.8) {
                return true;
            }
        }

        return false;
    }
}