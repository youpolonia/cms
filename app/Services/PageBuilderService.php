<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class PageBuilderService
{
    public function getAllPages()
    {
        return Page::latest()->get();
    }

    public function createPage(array $data)
    {
        return Page::create([
            'title' => $data['title'],
            'slug' => Str::slug($data['title']),
            'blocks' => json_decode($data['blocks'], true),
            'user_id' => auth()->id(),
            'ai_metadata' => $data['ai_metadata'] ?? null
        ]);
    }

    public function updatePage(Page $page, array $data)
    {
        $updateData = [];
        
        if (isset($data['title'])) {
            $updateData['title'] = $data['title'];
            $updateData['slug'] = Str::slug($data['title']);
        }

        if (isset($data['blocks'])) {
            $updateData['blocks'] = json_decode($data['blocks'], true);
        }

        if (isset($data['ai_metadata'])) {
            $updateData['ai_metadata'] = $data['ai_metadata'];
        }

        return $page->update($updateData);
    }

    public function deletePage(Page $page)
    {
        return $page->delete();
    }

    public function getPageBlocks(Page $page)
    {
        return $page->blocks;
    }

    public function updatePageBlocks(Page $page, array $blocks)
    {
        return $page->update(['blocks' => $blocks]);
    }

    public function generateContentSuggestion(string $prompt, string $type = 'blog_post')
    {
        $maxTokens = config("mcp.content_generation.content_types.$type.max_tokens", 1000);
        
        // Validate prompt length
        if (str_word_count($prompt) > 500) {
            throw new \InvalidArgumentException('Prompt too long. Maximum 500 words allowed.');
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('mcp.api_key'),
                'Content-Type' => 'application/json'
            ])->post('http://localhost:8080/generate/content', [
                'prompt' => $prompt,
                'model' => 'gpt-4-turbo',
                'max_tokens' => $maxTokens,
                'seo_optimized' => $type === 'blog_post'
            ]);

            if ($response->failed()) {
                throw new \RuntimeException('Content generation failed: ' . $response->body());
            }

            return [
                'content' => $response->json()['content'],
                'model_used' => $response->json()['model_used']
            ];
        } catch (\Exception $e) {
            \Log::error('Content generation error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function suggestBlocks(array $context)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('mcp.api_key'),
            'Content-Type' => 'application/json'
        ])->post(config('mcp.servers.content.base_uri') . '/suggest-blocks', [
            'context' => $context,
            'media_available' => true
        ]);

        return $response->json();
    }
}