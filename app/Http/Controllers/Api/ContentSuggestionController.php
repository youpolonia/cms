<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\OpenAIService;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;

class ContentSuggestionController extends Controller
{
    const MAX_CREDITS = 100;
    const CREDITS_PER_REQUEST = 5;
    const RATE_LIMIT = '10 per minute';

    public function getSuggestions(Request $request)
    {
        // Rate limiting
        $executed = RateLimiter::attempt(
            'ai-suggestions:'.$request->user()->id,
            self::RATE_LIMIT,
            function() {}
        );

        if (!$executed) {
            return response()->json([
                'message' => 'Too many requests'
            ], 429);
        }

        // Check credits
        $user = $request->user();
        if ($user->ai_usage_count >= self::MAX_CREDITS) {
            return response()->json([
                'message' => 'Insufficient credits'
            ], 403);
        }

        // Call OpenAI Service
        try {
            $openAiService = new OpenAIService();
            $result = $openAiService->generateContent(
                $request->prompt,
                ['template' => $request->template],
                $request->outputFormat ?? 'text',
                $request->includeImages ?? false
            );

            // Update usage
            $user->increment('ai_usage_count', self::CREDITS_PER_REQUEST);

            return response()->json([
                'content' => $result['content'],
                'usage' => $result['usage'],
                'cost' => $result['cost'],
                'images' => $result['images'] ?? [],
                'remaining_credits' => self::MAX_CREDITS - $user->ai_usage_count,
                'credits_used' => self::CREDITS_PER_REQUEST,
                'last_usage' => now()->toDateTimeString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate content: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getUsageStats(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'total_used' => $user->ai_usage_count,
            'remaining' => self::MAX_CREDITS - $user->ai_usage_count,
            'limit' => self::MAX_CREDITS,
            'rate_limit' => self::RATE_LIMIT
        ]);
    }

    protected function getSystemPrompt($template)
    {
        $prompts = [
            'content_suggestion' => 'You are a content suggestion assistant. Provide creative and engaging content ideas based on the user\'s prompt.',
            'seo_optimization' => 'You are an SEO expert. Provide SEO optimization suggestions for the given content.',
            'content_enhancement' => 'You are a content editor. Suggest improvements to make the content more engaging and effective.',
            'content_summary' => 'You are a summarization tool. Provide concise summaries of the given content.',
            'html_content' => 'You are a web content generator. Generate complete HTML content sections based on the prompt.',
            'json_content' => 'You are a structured content generator. Provide content in JSON format with title, body, and metadata fields.'
        ];

        return $prompts[$template] ?? $prompts['content_suggestion'];
    }
}
