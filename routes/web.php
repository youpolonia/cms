Route::resource('scheduled-exports', \App\Http\Controllers\ScheduledExportController::class)->except(['show']);
Route::get('scheduled-exports/{scheduled_export}', [\App\Http\Controllers\ScheduledExportController::class, 'show'])->name('scheduled-exports.show');
Route::post('scheduled-exports/{scheduled_export}/run-now', [\App\Http\Controllers\ScheduledExportController::class, 'runNow'])->name('scheduled-exports.run-now');
use App\Models\AnalyticsExport;
use App\Services\ExportTagService;
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ContentVersionController,
    VersionComparisonController,
    AIAnalyticsController,
    AnalyticsController,
    ContentController,
    ContentModerationController
};
use App\Http\Livewire\ContentVersionComparison;
use App\Livewire\Analytics\AiUsageDashboard;

Route::post('/content-versions/{version}/moderate', [ContentModerationController::class, 'moderate'])
    ->name('content-versions.moderate')
    ->middleware('auth');

Route::get('/content-versions/{version}/moderation-history', [ContentModerationController::class, 'history'])
    ->name('content-versions.moderation-history')
    ->middleware('auth');
use App\Models\ContentVersionDiff;
use App\View\Components\ContentDiffViewer;
use App\Http\Controllers\ContentScheduleController;
use App\Http\Controllers\TagsController;
Route::get('/test-export-tags', function (ExportTagService $tagService) {
    // Create or get test export
    $export = AnalyticsExport::firstOrCreate(
        ['name' => 'Test Export'],
        ['user_id' => 1, 'status' => 'pending']
    );

    // Example tag operations
    $tagService->assignTags($export, ['analytics', 'monthly']);
    
    return view('test-export-tags', [
        'export' => $export->load('tags'),
        'allTags' => $tagService->getAllTagsWithCounts()
    ]);
});
use App\Http\Controllers\AnalyticsExportController;

// Existing routes...

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('audit-logs', \App\Http\Controllers\AuditLogController::class)
        ->only(['index', 'show'])
        ->middleware('can:view_audit_logs');
});

Route::middleware(['auth', 'track.ai.usage'])->group(function () {
    Route::get('/analytics/ai-usage', AiUsageDashboard::class)->name('analytics.ai-usage');
    Route::get('/analytics/dashboard', [AnalyticsController::class, 'dashboard'])->name('analytics.dashboard');
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    
    // AI operation routes
    Route::prefix('ai')->group(function () {
        Route::post('/generate', [\App\Http\Controllers\AIController::class, 'generate'])->name('ai.generate');
        Route::post('/improve', [\App\Http\Controllers\AIController::class, 'improve'])->name('ai.improve');
        Route::post('/summarize', [\App\Http\Controllers\AIController::class, 'summarize'])->name('ai.summarize');
    });
});

Route::resource('tags', TagsController::class)->except(['show']);

// Content scheduling routes
Route::get('contents/{content}/schedule', [ContentScheduleController::class, 'create'])->name('contents.schedule.create');
Route::post('contents/{content}/schedule', [ContentScheduleController::class, 'store'])->name('contents.schedule.store');
Route::delete('contents/{content}/schedule', [ContentScheduleController::class, 'destroy'])->name('contents.schedule.destroy');
Route::get('contents/{content}/scheduling', [ContentScheduleController::class, 'index'])
    ->name('contents.scheduling')
    ->middleware('auth');

// Analytics exports routes
Route::resource('exports', AnalyticsExportController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
Route::get('exports/{export}/download', [AnalyticsExportController::class, 'download'])->name('exports.download');
Route::post('exports/{export}/extend', [AnalyticsExportController::class, 'extend'])->name('exports.extend');

// Demo route for content version diff viewer
Route::get('/diff-demo', function() {
    $diff = ContentVersionDiff::first();
    if (!$diff) {
        return "No version diffs found in database. Please create some content versions first.";
    }
    
    return (new ContentDiffViewer($diff))->render();
});

Route::get('/page-builder-demo', function () {
    return view('page-builder.demo');
})->name('page-builder.demo');


// Content management routes
// Moderation routes
Route::prefix('moderation')->middleware('auth')->group(function() {
    Route::get('/', [\App\Http\Controllers\ModerationController::class, 'index'])->name('moderation.index');
    Route::get('/history', [\App\Http\Controllers\ModerationController::class, 'history'])->name('moderation.history');
    Route::get('/{moderation}', [\App\Http\Controllers\ModerationController::class, 'show'])->name('moderation.show');
    Route::post('/{moderation}/approve', [\App\Http\Controllers\ModerationController::class, 'approve'])->name('moderation.approve');
    Route::post('/{moderation}/reject', [\App\Http\Controllers\ModerationController::class, 'reject'])->name('moderation.reject');
});
Route::resource('contents', ContentController::class)->middleware('auth');
Route::get('/content/list', \App\Http\Livewire\ContentList::class)->name('content.list')->middleware('auth');

// Category bulk operations
Route::prefix('categories')->middleware('auth')->group(function() {
    Route::post('/bulk-delete', [\App\Http\Controllers\CategoryBulkOperationsController::class, 'delete'])
        ->name('categories.bulk.delete');
        
    Route::post('/bulk-move', [\App\Http\Controllers\CategoryBulkOperationsController::class, 'move'])
        ->name('categories.bulk.move');
        
    Route::post('/bulk-status', [\App\Http\Controllers\CategoryBulkOperationsController::class, 'toggleStatus'])
        ->name('categories.bulk.status');
});

// Content version management routes
Route::middleware(['auth', 'can:manage_content_versions'])->group(function() {
    Route::post('content/{content}/versions', [ContentVersionController::class, 'createVersion'])
        ->name('content.versions.create');
    Route::post('content/{content}/versions/{version}/restore', [ContentVersionController::class, 'restoreVersion'])
        ->name('content.versions.restore');
    Route::get('content/{content}/versions/{version1}/diff/{version2}', [ContentVersionController::class, 'diff'])
        ->name('content.versions.diff');
});

require __DIR__.'/content_versions.php';

// Content version comparison
Route::get('/content/{content}/versions/compare', \App\Http\Livewire\ContentVersionComparison::class)
    ->name('content.versions.compare')
    ->middleware(['auth', 'can:view_content_versions']);

Route::prefix('content/recycle-bin')->name('content.recycle-bin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\ContentRecycleBinController::class, 'index'])->name('index');
    Route::post('/{id}/restore', [\App\Http\Controllers\ContentRecycleBinController::class, 'restore'])->name('restore');
    Route::delete('/{id}', [\App\Http\Controllers\ContentRecycleBinController::class, 'forceDelete'])->name('force-delete');
    Route::delete('/', [\App\Http\Controllers\ContentRecycleBinController::class, 'empty'])->name('empty');
});

// Categories
Route::resource('categories', \App\Http\Controllers\CategoryController::class)
    ->middleware('auth');

// Category content assignments
Route::prefix('categories/{category}')->middleware('auth')->group(function() {
    Route::get('/content', [\App\Http\Controllers\CategoryController::class, 'content'])
        ->name('categories.content.index');
        
    Route::post('/content', [\App\Http\Controllers\CategoryController::class, 'storeContent'])
        ->name('categories.content.store');
});
