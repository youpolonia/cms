<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AIService;
use App\Http\Requests\AIGenerateRequest;

class AIController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function generate(AIGenerateRequest $request)
    {
        $content = $this->aiService->generateContent(
            $request->input('prompt'),
            $request->input('tone'),
            $request->input('length')
        );

        return response()->json([
            'success' => true,
            'content' => $content
        ]);
    }

    public function improve(Request $request)
    {
        $improved = $this->aiService->improveContent(
            $request->input('content'),
            $request->input('instructions')
        );

        return response()->json([
            'success' => true,
            'improved_content' => $improved
        ]);
    }

    public function summarize(Request $request)
    {
        $summary = $this->aiService->summarizeContent(
            $request->input('content'),
            $request->input('length')
        );

        return response()->json([
            'success' => true,
            'summary' => $summary
        ]);
    }
}