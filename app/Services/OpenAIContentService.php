<?php

namespace App\Services;

use App\Contracts\ContentGenerationServiceInterface;
use OpenAI\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use OpenAI\Exceptions\ErrorException;

class OpenAIContentService implements ContentGenerationServiceInterface
{
    private Client $client;
    private string $model;
    private array $defaultParams;
    private float $totalCost = 0;
    private int $usageCount = 0;

    public function __construct(Client $client, string $model, array $defaultParams)
    {
        $this->client = $client;
        $this->model = $model;
        $this->defaultParams = $defaultParams;
    }

    public function generateContent(string $prompt, array $parameters = []): string
    {
        try {
            $params = array_merge($this->defaultParams, $parameters, [
                'model' => $this->model,
                'messages' => [['role' => 'user', 'content' => $prompt]]
            );

            if (!$this->isWithinRateLimit()) {
                throw new \RuntimeException('Rate limit exceeded');
            }

            $response = $this->client->chat()->create($params);
            $content = $response->choices[0]->message->content;

            $this->trackUsage($response->usage->totalTokens);
            return $content;

        } catch (ErrorException $e) {
            Log::error('OpenAI API error: ' . $e->getMessage());
            throw new \RuntimeException('Content generation failed: ' . $e->getMessage());
        }
    }

    public function getUsageCost(): float
    {
        return $this->totalCost;
    }

    public function getUsageCount(): int
    {
        return $this->usageCount;
    }

    public function getRemainingCredits(): float
    {
        $monthlyLimit = config('openai.monthly_limit', 100);
        return max(0, $monthlyLimit - $this->totalCost);
    }

    public function isWithinRateLimit(): bool
    {
        $key = 'openai_rate_limit_' . md5($this->model);
        $requests = Cache::get($key, 0);
        return $requests < config('openai.rate_limit.requests_per_minute', 60);
    }

    private function trackUsage(int $tokens): void
    {
        $this->usageCount++;
        $pricePerToken = config("openai.cost_tracking.price_per_token.{$this->model}", 0.000002);
        $this->totalCost += $tokens * $pricePerToken;

        $key = 'openai_rate_limit_' . md5($this->model);
        Cache::increment($key);
        Cache::put($key, Cache::get($key), now()->addMinute());
    }
}