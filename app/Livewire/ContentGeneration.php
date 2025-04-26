<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ContentGeneration extends Component
{
    public $prompt = '';
    public $model = 'gpt-4-turbo';
    public $maxTokens = 1000;
    public $generatedContent = '';
    public $error = '';
    public $tokenCount = 0;

    protected $availableModels = [
        'gpt-4-turbo' => 'GPT-4 Turbo',
        'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
        'llama-3-70b' => 'Llama 3 70B',
        'gemma-7b' => 'Gemma 7B'
    ];

    public function updatedPrompt($value)
    {
        $this->tokenCount = $this->estimateTokenCount($value);
    }

    protected function estimateTokenCount(string $text): int
    {
        // Rough estimation: 1 token ~= 4 characters
        return ceil(Str::length($text) / 4);
    }

    public function generateContent()
    {
        $this->resetError();
        
        try {
            $response = Http::post(config('mcp.content_generation.url'), [
                'prompt' => $this->prompt,
                'model' => $this->model,
                'max_tokens' => $this->maxTokens
            ]);

            if ($response->successful()) {
                $this->generatedContent = $response->json('content');
            } else {
                $this->error = $response->json('message', 'Failed to generate content');
            }
        } catch (\Exception $e) {
            $this->error = 'Content generation error: ' . $e->getMessage();
        }
    }

    protected function resetError()
    {
        $this->error = '';
    }

    public function getAvailableModelsProperty()
    {
        return $this->availableModels;
    }

    public function render()
    {
        return view('livewire.content-generation');
    }
}
