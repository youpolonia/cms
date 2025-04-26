<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PhpContentGenerationService
{
    public function generateContent(string $prompt, string $model = 'gpt-3.5-turbo'): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.openai.key'),
            'Content-Type' => 'application/json'
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => $model,
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'max_tokens' => 1000
        ]);

        return $response->json('choices.0.message.content');
    }

    public function summarizeText(string $text): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.openai.key')
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'Summarize this text concisely'],
                ['role' => 'user', 'content' => $text]
            ],
            'max_tokens' => 300
        ]);

        return $response->json('choices.0.message.content');
    }

    public function generateSeo(string $topic): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.openai.key')
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'Generate SEO keywords and meta description for this topic'],
                ['role' => 'user', 'content' => $topic]
            ],
            'max_tokens' => 500
        ]);

        $content = $response->json('choices.0.message.content');
        
        return [
            'keywords' => $this->extractKeywords($content),
            'description' => $this->extractDescription($content)
        ];
    }

    private function extractKeywords(string $content): string
    {
        // Implementation to extract keywords from response
        return $content;
    }

    private function extractDescription(string $content): string
    {
        // Implementation to extract description from response
        return $content;
    }
}