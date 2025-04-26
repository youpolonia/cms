<?php

use App\Http\Controllers\ContentBranchingController;
use App\Http\Controllers\ContentVersionComparisonController;
use App\Http\Controllers\ContentVersionHistoryController;
use App\Http\Controllers\ContentVersionRestorationController;
use Illuminate\Support\Facades\Route;

Route::prefix('content/{content}/versions')->group(function () {
    // Version history route
    Route::get('/', [ContentVersionHistoryController::class, 'index'])
        ->name('content.versions.index');
    
    // Version comparison routes
    Route::prefix('compare')->group(function () {
        Route::get('/', [ContentVersionComparisonController::class, 'index'])
            ->name('content.version-comparison.index');
        Route::post('/', [ContentVersionComparisonController::class, 'compare'])
            ->name('content.version-comparison.compare');
        Route::get('/{diff}', [ContentVersionComparisonController::class, 'show'])
            ->name('content.version-comparison.show');
        Route::delete('/{diff}', [ContentVersionComparisonController::class, 'destroy'])
            ->name('content.version-comparison.destroy');
        Route::get('/analytics', [ContentVersionComparisonController::class, 'analytics'])
            ->name('content.version-comparison.analytics');
    });
    
    // Direct version comparison route
    Route::get('/compare/{oldVersion}/{newVersion}', [ContentVersionRestorationController::class, 'compare'])
        ->name('content.versions.compare');
    
    // Version resource routes
    Route::get('/{version}', [ContentVersionRestorationController::class, 'show'])
        ->name('content.versions.show');
    Route::put('/{version}', [ContentVersionRestorationController::class, 'update'])
        ->name('content.versions.update');
    Route::delete('/{version}', [ContentVersionRestorationController::class, 'destroy'])
        ->name('content.versions.destroy');

    // Version comparison route
    Route::get('/{version}/compare', [ContentVersionRestorationController::class, 'compare'])
        ->name('content.versions.compare');
        
    // Rollback preparation route
    Route::post('/{version}/prepare-restore', [ContentVersionRestorationController::class, 'prepareRestore'])
        ->name('content.versions.prepare-restore');
        
    // Rollback confirmation route
    Route::post('/{version}/confirm-restore', [ContentVersionRestorationController::class, 'confirmRestore'])
        ->name('content.versions.confirm-restore');
        
    // Version analytics route
    Route::get('/{version}/analytics', [ContentVersionRestorationController::class, 'analytics'])
        ->name('content.versions.analytics');
        
    // Branch management routes
    Route::prefix('branches')->group(function () {
        Route::post('/', [ContentBranchingController::class, 'store'])
            ->middleware(['auth', 'verified'])
            ->name('content.branches.store');
            
        Route::get('/', [ContentBranchingController::class, 'index'])
            ->middleware(['auth', 'verified'])
            ->name('content.branches.index');
            
        Route::post('/{branch}/merge', [ContentBranchingController::class, 'merge'])
            ->middleware(['auth', 'verified'])
            ->name('content.branches.merge');
    });
});