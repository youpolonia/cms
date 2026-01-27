<?php

namespace Includes\AI;

use Includes\Validation\ValidationHelper;
use Includes\Core\Logger;

final class SuggestionEngine
{
    private array $context = [];
    private array $versionHistory = [];
    private array $performanceMetrics = [];

    public function __construct(
        private string $apiKey,
        private string $model = 'gpt-3.5-turbo'
    ) {
        $this->validateApiKey($apiKey);
    }

    public function getSuggestions(string $content, array $options = []): array
    {
        $this->trackPerformance('start');
        $this->validateContent($content);
        
        $prompt = $this->buildPrompt($content, $options);
        $suggestions = $this->callAI($prompt);
        
        $this->trackPerformance('end');
        $this->logVersionInteraction($content, $suggestions);
        
        return ValidationHelper::validateOrFail($suggestions, [
            'suggestions' => 'required|array',
            'reasoning' => 'required|string'
        ]);
    }

    private function buildPrompt(string $content, array $options): string
    {
        $context = $this->context ? "\nContext: " . json_encode($this->context) : '';
        $history = $this->versionHistory ? "\nHistory: " . json_encode($this->versionHistory) : '';
        $goal = isset($options['goal']) ? $options['goal'] : 'improve clarity and engagement';

        return <<<PROMPT
        Analyze this content and provide improvement suggestions:
        {$content}
        {$context}
        {$history}
        Options: {$goal}
        PROMPT;
    }

    private function callAI(string $prompt): array
    {
        // Implementation would call actual AI API
        return [
            'suggestions' => ['Consider shortening sentences', 'Add more examples'],
            'reasoning' => 'Content could be more concise and illustrative'
        ];
    }

    private function validateContent(string $content): void
    {
        ValidationHelper::validateOrFail(['content' => $content], [
            'content' => 'required|string|min:10'
        ]);
    }

    private function validateApiKey(string $apiKey): void
    {
        ValidationHelper::validateOrFail(['api_key' => $apiKey], [
            'api_key' => 'required|string|min:32'
        ]);
    }

    private function trackPerformance(string $stage): void
    {
        $this->performanceMetrics[$stage] = microtime(true);
    }

    private function logVersionInteraction(string $content, array $suggestions): void
    {
        Logger::log('ai_suggestion', [
            'content_hash' => md5($content),
            'suggestions' => $suggestions,
            'performance' => $this->performanceMetrics
        ]);
    }
}
