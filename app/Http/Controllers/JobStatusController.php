<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class JobStatusController extends Controller
{
    public function checkStatus(Request $request)
    {
        $validated = $request->validate([
            'cache_key' => 'required|string'
        ]);

        if (!Cache::has($validated['cache_key'])) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Job result not found or expired'
            ], 404);
        }

        return response()->json([
            'status' => 'completed',
            'result' => Cache::get($validated['cache_key'])
        ]);
    }
}