<?php

namespace App\Http\Controllers;

use App\Services\OpenAIService;
use App\Services\AiUsageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContentGenerationController extends Controller
{
    protected OpenAIService $openAIService;
    protected AiUsageService $aiUsageService;

    public function __construct(OpenAIService $openAIService, AiUsageService $aiUsageService)
    {
        $this->openAIService = $openAIService;
        $this->aiUsageService = $aiUsageService;
    }

    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string',
            'content_type' => 'required|string',
            'tone' => 'required|string|in:professional,casual,friendly,authoritative,creative,technical',
            'length' => 'required|string|in:short,medium,long,extended',
            'style' => 'required|string',
            'target_audience' => 'required|string',
            'model' => 'sometimes|string',
            'language' => 'sometimes|string',
            'validation_rules' => 'sometimes|array',
            'examples' => 'sometimes|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $params = $validator->validated();
            
            // Check usage limits first
            if (!$this->aiUsageService->canUseAi($request->user())) {
                return response()->json([
                    'error' => 'Usage limit exceeded',
                    'message' => 'You have reached your AI usage limit'
                ], 429);
            }
            
            $result = $this->openAIService->generateContent($params);
            
            // Track token usage
            $tokens = $this->estimateTokenUsage($params);
            $this->aiUsageService->recordUsage($request->user(), $tokens);
            
            return response()->json(array_merge($result, [
                'tokens_used' => $tokens
            ]));
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 429);
        }
    }

    public function suggestCategories(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string',
            'language' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->openAIService->suggestCategories(
                $validator->validated()['prompt'],
                $validator->validated()['language'] ?? 'English'
            );
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 429);
        }
    }

    /**
     * Get current usage stats
     */
    public function usageStats(Request $request)
    {
        $user = $request->user();
        $usage = $this->aiUsageService->getUserUsage($user);
        
        return response()->json([
            'daily' => $usage['daily'],
            'daily_limit' => $usage['daily_limit'],
            'daily_remaining' => $usage['daily_remaining'],
            'monthly' => $usage['monthly'],
            'monthly_limit' => $usage['monthly_limit'],
            'monthly_remaining' => $usage['monthly_remaining'],
            'can_use' => $this->aiUsageService->canUseAi($user)
        ]);
    }
    
    /**
     * Check if user can perform AI operation
     */
    public function checkUsage(Request $request)
    {
        $user = $request->user();
        $canUse = $this->aiUsageService->canUseAi($user);
        $usage = $this->aiUsageService->getUserUsage($user);
        
        return response()->json([
            'can_use' => $canUse,
            'daily_usage' => $usage['daily'],
            'daily_limit' => $usage['daily_limit'],
            'monthly_usage' => $usage['monthly'],
            'monthly_limit' => $usage['monthly_limit'],
            'remaining' => $canUse ? min($usage['daily_remaining'], $usage['monthly_remaining']) : 0,
            'limit_type' => !$canUse ?
                ($this->aiUsageService->isDailyLimitExceeded($user) ? 'daily' : 'monthly') : null
        ]);
    }

    public function getAvailableModels()
    {
        try {
            $result = $this->openAIService->getAvailableModels();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estimate token usage based on request parameters
     */
    protected function estimateTokenUsage(array $params): int
    {
        $baseTokens = 100; // Base tokens for system message and formatting
        
        // Add tokens for prompt
        $baseTokens += ceil(mb_strlen($params['prompt']) / 4);
        
        // Add tokens for content type and options
        $baseTokens += 50;
        
        // Multiply by length factor
        switch ($params['length']) {
            case 'short': $baseTokens *= 1; break;
            case 'medium': $baseTokens *= 1.5; break;
            case 'long': $baseTokens *= 2; break;
            case 'extended': $baseTokens *= 3; break;
        }
        
        // Model multiplier
        if (isset($params['model']) && $params['model'] === 'gpt-4') {
            $baseTokens *= 1.3;
        }
        
        return (int) $baseTokens;
    }
}