<?php

namespace App\Services;

use App\Notifications\AIUsageThresholdAlert;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class AIService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.openai.key');
    }

    public function generateContent(string $prompt, string $tone = 'professional', string $length = 'medium')
    {
        $response = $this->makeRequest('/completions', [
            'model' => 'text-davinci-003',
            'prompt' => $prompt,
            'max_tokens' => $this->getTokenLength($length),
            'temperature' => 0.7,
        ]);

        $this->trackUsage(auth()->user(), 'generate', $response['usage']['total_tokens']);

        return $response['choices'][0]['text'];
    }

    public function improveContent(string $content, string $instructions)
    {
        $response = $this->makeRequest('/edits', [
            'model' => 'text-davinci-edit-001',
            'input' => $content,
            'instruction' => $instructions,
            'temperature' => 0.7,
        ]);

        $this->trackUsage(auth()->user(), 'improve', $response['usage']['total_tokens']);

        return $response['choices'][0]['text'];
    }

    public function summarizeContent(string $content, string $length = 'medium')
    {
        $response = $this->makeRequest('/completions', [
            'model' => 'text-davinci-003',
            'prompt' => "Summarize this content in $length length:\n\n$content",
            'max_tokens' => $this->getTokenLength($length),
            'temperature' => 0.5,
        ]);

        $this->trackUsage(auth()->user(), 'summarize', $response['usage']['total_tokens']);

        return $response['choices'][0]['text'];
    }

    protected function makeRequest(string $endpoint, array $data)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . $endpoint, $data);

        if ($response->failed()) {
            Log::error('AI API request failed', [
                'status' => $response->status(),
                'response' => $response->json()
            ]);
            throw new \Exception('AI service request failed');
        }

        return $response->json();
    }

    protected function getTokenLength(string $length): int
    {
        return match($length) {
            'short' => 100,
            'long' => 500,
            default => 300, // medium
        };
    }

    protected function trackUsage(User $user, string $action, int $tokens)
    {
        $user->increment('ai_usage_count', $tokens);
        $user->increment('ai_monthly_usage', $tokens);

        $thresholds = config('ai.thresholds');
        $currentUsage = $user->ai_monthly_usage;

        foreach ($thresholds as $limit => $type) {
            if ($currentUsage >= $limit && $currentUsage - $tokens < $limit) {
                $user->notify(new AIUsageThresholdAlert($type, $currentUsage));
            }
        }
    }
}