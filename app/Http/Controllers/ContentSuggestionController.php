<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OpenAIService;

class ContentSuggestionController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    public function generate(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string',
            'outputFormat' => 'sometimes|in:text,html,json',
            'template' => 'sometimes|in:content_suggestion,seo_optimization,content_enhancement,content_summary',
            'includeImages' => 'sometimes|boolean'
        ]);

        try {
            $result = $this->openAIService->generateContent(
                $request->input('prompt'),
                $request->input('template', 'content_suggestion'),
                $request->input('outputFormat', 'text'),
                $request->input('includeImages', false)
            );

            return response()->json([
                'content' => $result['content'],
                'images' => $result['images'] ?? [],
                'usage' => [
                    'tokens' => $result['usage']['total_tokens'],
                    'cost' => $result['usage']['cost']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate content: ' . $e->getMessage()
            ], 500);
        }
    }
}