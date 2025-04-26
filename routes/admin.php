<?php

use App\Http\Controllers\ModerationQueueController;
use App\Http\Controllers\ContentScheduleController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\ContentAnalyticsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'can:moderate_content'])->group(function () {
    Route::get('moderation', [ModerationQueueController::class, 'index'])
        ->name('moderation.index');
    Route::get('moderation/{moderation}', [ModerationQueueController::class, 'show'])
        ->name('moderation.show');
    Route::post('moderation/{moderation}/approve', [ModerationQueueController::class, 'approve'])
        ->name('moderation.approve');
    Route::post('moderation/{moderation}/reject', [ModerationQueueController::class, 'reject'])
        ->name('moderation.reject');
});

Route::middleware(['auth', 'can:manage_content'])->group(function () {
    Route::resource('contents', \App\Http\Controllers\ContentController::class);
    Route::post('contents/{content}/categories', [\App\Http\Controllers\ContentController::class, 'syncCategories'])
        ->name('contents.categories.sync');
    Route::get('content-analytics', [ContentAnalyticsController::class, 'index'])
        ->name('content.analytics');
    Route::get('content-analytics/data', [ContentAnalyticsController::class, 'getContentAnalytics'])
        ->name('content.analytics.data');
});

Route::middleware(['auth', 'can:manage_content'])->group(function () {
    Route::resource('content/schedules', ContentScheduleController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::get('content/{content}/schedules', [ContentScheduleController::class, 'contentSchedules'])
        ->name('content.schedules.index');
});

Route::middleware(['auth', 'verified', 'can:admin'])->group(function () {
    Route::resource('categories', CategoryController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('content', ContentController::class);
});