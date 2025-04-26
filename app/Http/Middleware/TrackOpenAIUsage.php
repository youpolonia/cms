<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TrackOpenAIUsage
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($response->isSuccessful() && $request->routeIs('ai.openai.*')) {
            try {
                $user = $request->user();
                $content = json_decode($response->getContent(), true);
                $tokensUsed = $content['usage']['total_tokens'] ?? 0;
                $model = $content['model'] ?? 'unknown';

                $costPerToken = config("ai.openai.cost_per_token.$model", 0.00002);
                $cost = $tokensUsed * $costPerToken;

                User::where('id', $user->id)->increment('ai_usage_count', $tokensUsed);
                User::where('id', $user->id)->increment('ai_usage_cost', $cost);

                Log::info("OpenAI API Usage", [
                    'user_id' => $user->id,
                    'tokens' => $tokensUsed,
                    'model' => $model,
                    'cost' => $cost,
                    'route' => $request->route()->getName()
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to track OpenAI usage', [
                    'error' => $e->getMessage(),
                    'route' => $request->route()->getName()
                ]);
            }
        }

        return $response;
    }
}