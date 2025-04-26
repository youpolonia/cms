<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\ModerationQueue;
use App\Services\OpenAIModerationService;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;

class OpenAIContentController extends Controller
{
    protected OpenAIModerationService $moderationService;

    public function __construct(OpenAIModerationService $moderationService)
    {
        $this->moderationService = $moderationService;
    }

    public function generateContent(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'content_type' => 'required|string|in:article,summary,description',
            'tone' => 'sometimes|string|in:professional,casual,enthusiastic',
            'length' => 'sometimes|integer|min:50|max:2000'
        ]);

        try {
            $response = OpenAI::completions()->create([
                'model' => 'text-davinci-003',
                'prompt' => $this->buildPrompt($request),
                'max_tokens' => $request->length ?? 500,
                'temperature' => 0.7,
            ]);

            $generatedText = $response->choices[0]->text;
            $moderationResults = $this->moderationService->moderateContent($generatedText);

            $content = Content::create([
                'title' => 'AI Generated: ' . substr($request->prompt, 0, 50),
                'body' => $generatedText,
                'content_type' => $request->content_type,
                'user_id' => auth()->id(),
                'is_ai_generated' => true,
                'ai_metadata' => [
                    'prompt' => $request->prompt,
                    'model' => 'text-davinci-003',
                    'moderation' => $moderationResults
                ]
            ]);

            if ($moderationResults['flagged'] || $request->content_type === 'article') {
                ModerationQueue::create([
                    'content_id' => $content->id,
                    'user_id' => auth()->id(),
                    'is_ai_generated' => true,
                    'ai_generation_metadata' => $content->ai_metadata,
                    'openai_moderation_results' => $moderationResults,
                    'moderation_policy' => 'strict',
                    'status' => 'pending'
                ]);
            }

            return response()->json([
                'success' => true,
                'content' => $content,
                'moderation' => $moderationResults
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Content generation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function buildPrompt(Request $request): string
    {
        $tone = $request->tone ?? 'professional';
        $type = $request->content_type;

        $prompts = [
            'article' => "Write a $tone article about: {$request->prompt}",
            'summary' => "Create a $tone summary of: {$request->prompt}",
            'description' => "Write a $tone description for: {$request->prompt}"
        ];

        return $prompts[$type] ?? $prompts['article'];
    }
}