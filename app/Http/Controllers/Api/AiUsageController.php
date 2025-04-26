<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContentUserView;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AiUsageController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'timeframe' => 'sometimes|in:today,week,month,year,all',
            'content_id' => 'sometimes|exists:contents,id',
            'user_id' => 'sometimes|exists:users,id'
        ]);

        $query = ContentUserView::query()
            ->select([
                DB::raw('COUNT(*) as total_views'),
                DB::raw('SUM(used_ai_generation) as ai_generation_uses'),
                DB::raw('SUM(used_ai_editing) as ai_editing_uses'),
                DB::raw('SUM(used_ai_suggestions) as ai_suggestions_uses'),
                DB::raw('COUNT(DISTINCT user_id) as unique_users')
            ]);

        if ($request->has('timeframe')) {
            $query->whereBetween('created_at', $this->getTimeframeRange($validated['timeframe']));
        }

        if ($request->has('content_id')) {
            $query->where('content_id', $validated['content_id']);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $validated['user_id']);
        }

        return response()->json($query->first());
    }

    public function topUsers(Request $request)
    {
        $validated = $request->validate([
            'limit' => 'sometimes|integer|min:1|max:100',
            'timeframe' => 'sometimes|in:today,week,month,year,all'
        ]);

        $limit = $request->input('limit', 10);

        $query = User::query()
            ->select([
                'users.id',
                'users.name',
                'users.email',
                DB::raw('COUNT(content_user_views.id) as total_views'),
                DB::raw('SUM(content_user_views.used_ai_generation) as ai_generation_uses'),
                DB::raw('SUM(content_user_views.used_ai_editing) as ai_editing_uses'),
                DB::raw('SUM(content_user_views.used_ai_suggestions) as ai_suggestions_uses'),
                DB::raw('ai_usage_count as total_ai_uses')
            ])
            ->join('content_user_views', 'content_user_views.user_id', '=', 'users.id')
            ->groupBy('users.id')
            ->orderByDesc('total_ai_uses')
            ->limit($limit);

        if ($request->has('timeframe')) {
            $query->whereBetween('content_user_views.created_at', $this->getTimeframeRange($validated['timeframe']));
        }

        return response()->json($query->get());
    }

    public function toolUsage(Request $request)
    {
        $validated = $request->validate([
            'timeframe' => 'sometimes|in:today,week,month,year,all'
        ]);

        $query = ContentUserView::query()
            ->select([
                DB::raw('json_each.value as tool_name'),
                DB::raw('COUNT(*) as usage_count')
            ])
            ->join(DB::raw('json_each(content_user_views.ai_tools_accessed)'))
            ->groupBy('tool_name')
            ->orderByDesc('usage_count');

        if ($request->has('timeframe')) {
            $query->whereBetween('content_user_views.created_at', $this->getTimeframeRange($validated['timeframe']));
        }

        return response()->json($query->get());
    }

    protected function getTimeframeRange($timeframe)
    {
        return match($timeframe) {
            'today' => [now()->startOfDay(), now()],
            'week' => [now()->startOfWeek(), now()],
            'month' => [now()->startOfMonth(), now()],
            'year' => [now()->startOfYear(), now()],
            default => [DB::raw('1970-01-01'), now()],
        };
    }
}
