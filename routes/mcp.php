<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\ContentVersionRestorationController;
use App\Services\ContentSchedulingService;

Route::prefix('content')->group(function () {
    // Schedule content publishing
    Route::post('/{content}/schedule-publish', function ($contentId, ContentSchedulingService $service) {
        $content = Content::findOrFail($contentId);
        $service->schedulePublish(
            $content,
            Carbon::parse(request('publish_at'))
        );
        return response()->json(['success' => true]);
    });

    // Schedule content unpublishing
    Route::post('/{content}/schedule-unpublish', function ($contentId, ContentSchedulingService $service) {
        $content = Content::findOrFail($contentId);
        $service->scheduleUnpublish(
            $content,
            Carbon::parse(request('expire_at'))
        );
        return response()->json(['success' => true]);
    });

    // Cancel scheduling
    Route::post('/{content}/cancel-schedule', function ($contentId, ContentSchedulingService $service) {
        $content = Content::findOrFail($contentId);
        $service->cancelSchedule($content);
        return response()->json(['success' => true]);
    });

    // Get scheduling stats
    Route::get('/scheduled-stats', function (ContentSchedulingService $service) {
        return response()->json($service->getScheduledContent());
    });
});

Route::prefix('versions')->group(function () {
    // Compare two versions
    Route::post('/compare', function () {
        $versionA = ContentVersion::findOrFail(request('version_a_id'));
        $versionB = ContentVersion::findOrFail(request('version_b_id'));
        
        return response()->json(
            $versionA->compareWith($versionB)
        );
    });

    // Get comparison results
    Route::get('/{versionA}/compare-with/{versionB}', function ($versionAId, $versionBId) {
        $versionA = ContentVersion::findOrFail($versionAId);
        $versionB = ContentVersion::findOrFail($versionBId);
        
        $comparison = $versionA->getComparisonWith($versionB);
        
        if (!$comparison) {
            return response()->json([
                'error' => 'Comparison not available'
            ], 404);
        }

        return response()->json($comparison);
    });
});
use Illuminate\Http\Request;
use App\Http\Controllers\KnowledgeController;
use App\Services\SSEServerTransport;

Route::prefix('mcp')->middleware(['api', \App\Http\Middleware\VerifyCsrfToken::class])->group(function() {
    // Protocol version endpoint
    Route::get('/version', function() {
        return response()->json(['version' => '1.0.0']);
    });

    // Health check endpoint
    Route::get('/health', function() {
        return response()->json(['status' => 'healthy']);
    });

    // Authentication verification
    Route::get('/auth/verify', function(Request $request) {
        return response()->json([
            'authenticated' => true,
            'user' => $request->user()?->only('id', 'name', 'email')
        ]);
    });
    
    // MCP Protocol SSE Endpoint
    Route::get('/', function(Request $request) {
        $transport = new SSEServerTransport();
        $transport->handle($request);
        return response()->stream(function() use ($transport) {
            $transport->process();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    });

    // Content Generation Endpoints
    Route::prefix('content')->group(function() {
        Route::post('/generate', [\App\Http\Controllers\ContentGenerationController::class, 'generateContent']);
        Route::post('/summary', [\App\Http\Controllers\ContentGenerationController::class, 'generateSummary']);
        Route::post('/seo', [\App\Http\Controllers\ContentGenerationController::class, 'generateSEO']);
        Route::post('/improve', [\App\Http\Controllers\ContentGenerationController::class, 'improveContent']);
    });

    // Resource availability
    Route::get('/resources', function() {
        return response()->json([
            'available' => true,
            'resources' => ['knowledge', 'analytics', 'content']
        ]);
    });

    // Performance metrics
    Route::get('/metrics', function() {
        return response()->json([
            'memory' => memory_get_usage(),
            'cpu' => sys_getloadavg()[0],
            'response_time' => 0
        ]);
    });

    // Knowledge endpoints
    Route::post('/store', [KnowledgeController::class, 'store']);
    Route::get('/retrieve/{key}', [KnowledgeController::class, 'retrieve']);
    Route::post('/search', [KnowledgeController::class, 'search']);

    // Content version comparison
    Route::get('/content/compare/{baseId}/{compareId}', [ContentVersionRestorationController::class, 'apiCompare']);
});