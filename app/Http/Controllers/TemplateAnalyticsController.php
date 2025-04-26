<?php

namespace App\Http\Controllers;

use App\Models\TemplateAnalytics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TemplateAnalyticsController extends Controller
{
    /**
     * Track template usage event
     */
    public function track(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|exists:templates,id',
            'action' => 'required|in:created,applied,duplicated',
            'block_id' => 'nullable|exists:blocks,id',
            'metadata' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $analytics = TemplateAnalytics::create([
            'template_id' => $request->template_id,
            'user_id' => $request->user()->id,
            'block_id' => $request->block_id,
            'action' => $request->action,
            'metadata' => $request->metadata
        ]);

        return response()->json([
            'message' => 'Template usage tracked',
            'data' => $analytics
        ], 201);
    }

    /**
     * Get template usage statistics
     */
    public function stats($templateId)
    {
        $stats = TemplateAnalytics::query()
            ->where('template_id', $templateId)
            ->selectRaw('
                COUNT(*) as total_uses,
                SUM(CASE WHEN action = "applied" THEN 1 ELSE 0 END) as applied_count,
                SUM(CASE WHEN action = "created" THEN 1 ELSE 0 END) as created_count,
                SUM(CASE WHEN action = "duplicated" THEN 1 ELSE 0 END) as duplicated_count,
                COUNT(DISTINCT user_id) as unique_users
            ')
            ->first();

        return response()->json([
            'data' => $stats
        ]);
    }
}