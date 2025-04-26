<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ModerationQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class ModerationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ModerationQueue::with('content')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'content_id' => 'required|exists:contents,id'
        ]);

        try {
            $moderationResult = $this->moderateContent($request->content);
            
            $queueItem = ModerationQueue::create([
                'content_id' => $request->content_id,
                'content' => $request->content,
                'status' => $moderationResult['flagged'] ? 'flagged' : 'approved',
                'flags' => $moderationResult['categories'] ?? [],
                'score' => $moderationResult['score'] ?? 0
            ]);

            return response()->json([
                'status' => $queueItem->status,
                'flags' => $queueItem->flags,
                'score' => $queueItem->score
            ]);

        } catch (\Exception $e) {
            Log::error('Moderation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Moderation service unavailable'], 503);
        }
    }

    /**
     * Moderate content using OpenAI API
     */
    protected function moderateContent(string $content): array
    {
        $response = OpenAI::moderations()->create([
            'input' => $content,
            'model' => 'text-moderation-latest'
        ]);

        return [
            'flagged' => $response->results[0]->flagged,
            'categories' => $response->results[0]->categories,
            'score' => $response->results[0]->category_scores
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(ModerationQueue $moderationQueue)
    {
        return $moderationQueue->load('content');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ModerationQueue $moderationQueue)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,flagged'
        ]);

        $moderationQueue->update(['status' => $request->status]);

        return response()->json($moderationQueue);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ModerationQueue $moderationQueue)
    {
        $moderationQueue->delete();
        return response()->noContent();
    }

    /**
     * Process multiple content items for moderation
     */
    public function batchModerate(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.content' => 'required|string',
            'items.*.content_id' => 'required|exists:contents,id'
        ]);

        $results = [];
        foreach ($request->items as $item) {
            try {
                $moderationResult = $this->moderateContent($item['content']);
                
                $queueItem = ModerationQueue::create([
                    'content_id' => $item['content_id'],
                    'content' => $item['content'],
                    'status' => $moderationResult['flagged'] ? 'flagged' : 'approved',
                    'flags' => $moderationResult['categories'] ?? [],
                    'score' => $moderationResult['score'] ?? 0
                ]);

                $results[] = [
                    'content_id' => $item['content_id'],
                    'status' => $queueItem->status,
                    'flags' => $queueItem->flags,
                    'score' => $queueItem->score
                ];
            } catch (\Exception $e) {
                Log::error('Batch moderation failed for content: ' . $item['content_id']);
                $results[] = [
                    'content_id' => $item['content_id'],
                    'error' => 'Moderation failed'
                ];
            }
        }

        return response()->json($results);
    }
}
