<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('templates')->group(function () {
    Route::get('/', [\App\Http\Controllers\TemplateController::class, 'index']);
    Route::post('/', [\App\Http\Controllers\TemplateController::class, 'store']);
    Route::post('/{template}/apply', [\App\Http\Controllers\TemplateController::class, 'apply']);
    Route::delete('/{template}', [\App\Http\Controllers\TemplateController::class, 'destroy']);
    Route::get('/categories', [\App\Http\Controllers\TemplateController::class, 'categories']);
    Route::post('/analytics', [\App\Http\Controllers\TemplateAnalyticsController::class, 'track']);
    Route::get('/analytics/{template}', [\App\Http\Controllers\TemplateAnalyticsController::class, 'stats']);
});

Route::prefix('pages')->group(function () {
    Route::post('/{page}/save', [\App\Http\Controllers\PageController::class, 'save']);
    Route::get('/{page}/blocks', [\App\Http\Controllers\PageController::class, 'blocks']);
});

Route::prefix('blocks')->group(function () {
    Route::post('/', [\App\Http\Controllers\BlockController::class, 'store']);
    Route::put('/{block}', [\App\Http\Controllers\BlockController::class, 'update']);
    Route::delete('/{block}', [\App\Http\Controllers\BlockController::class, 'destroy']);
    Route::post('/reorder', [\App\Http\Controllers\BlockController::class, 'reorder']);
    Route::post('/{block}/duplicate', [\App\Http\Controllers\BlockController::class, 'duplicate']);
});
