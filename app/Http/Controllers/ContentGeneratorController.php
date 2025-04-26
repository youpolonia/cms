<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use App\Models\Content;
use App\Models\Category;
use Illuminate\Support\Str;

class ContentGeneratorController extends Controller
{
    public function suggestCategories(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:500'
        ]);

        try {
            $response = OpenAI::completions()->create([
                'model' => 'text-davinci-003',
                'prompt' => "Analyze this content prompt and suggest 3-5 relevant categories:\n\n{$request->prompt}\n\nSuggested categories (comma separated):",
                'max_tokens' => 100,
                'temperature' => 0.7,
            ]);

            $suggestions = array_map('trim', explode(',', $response->choices[0]->text));
            $suggestions = array_filter($suggestions);
            
            $categories = Category::whereIn('name', $suggestions)
                ->orWhere(function($query) use ($suggestions) {
                    foreach ($suggestions as $suggestion) {
                        $query->orWhere('name', 'like', "%{$suggestion}%");
                    }
                })
                ->limit(5)
                ->get();

            return response()->json($categories);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate category suggestions'
            ], 500);
        }
    }

    public function generate(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:500',
            'content_type' => 'required|string',
            'tone' => 'required|string',
            'length' => 'required|string',
            'style' => 'required|string',
            'model' => 'sometimes|string'
        ]);

        try {
            $systemPrompt = $this->buildSystemPrompt(
                $request->content_type,
                $request->tone,
                $request->length,
                $request->style
            );

            $response = OpenAI::chat()->create([
                'model' => $request->model ?? 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $request->prompt]
                ],
                'temperature' => 0.7,
            ]);

            $content = $response->choices[0]->message->content;

            // Save generation history
            $generatedContent = Content::create([
                'user_id' => auth()->id(),
                'title' => 'Generated: ' . Str::limit($request->prompt, 50),
                'content' => $content,
                'status' => 'generated',
                'meta' => [
                    'prompt' => $request->prompt,
                    'settings' => $request->only([
                        'content_type', 'tone', 'length', 'style', 'model'
                    ])
                ]
            ]);

            return response()->json([
                'success' => true,
                'content' => $content,
                'content_id' => $generatedContent->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate content: ' . $e->getMessage()
            ], 500);
        }
    }

    public function history()
    {
        $history = Content::where('user_id', auth()->id())
            ->where('status', 'generated')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json($history);
    }

    protected function buildSystemPrompt($type, $tone, $length, $style)
    {
        $lengths = [
            'short' => 'about 300 words',
            'medium' => 'about 500 words',
            'long' => 'about 800 words'
        ];

        return "You are a professional content writer. Create a {$tone} {$type} in a {$style} style. 
        The content should be {$lengths[$length]}. Use proper formatting, headings, and paragraphs. 
        Ensure the content is original, engaging, and well-structured.";
    }
}