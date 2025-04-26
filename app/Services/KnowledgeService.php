<?php

namespace App\Services;

use App\Models\Content;
use Illuminate\Support\Facades\Route;

class KnowledgeService
{
    public function getKnowledgeContent()
    {
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return collect();
        }

        return Content::where('is_knowledge', true)->get();
    }

    public function searchKnowledgeContent(string $query, int $limit = 10)
    {
        return Cache::remember("knowledge_search:{$query}", now()->addHours(1), function() use ($query, $limit) {
            return Content::where('is_knowledge', true)
                ->where(function($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhere('content', 'like', "%{$query}%");
                })
                ->limit($limit)
                ->get();
        });
    }

    public function registerRoutes()
    {
        Route::prefix('knowledge')->group(function() {
            Route::get('/', [\App\Http\Controllers\KnowledgeController::class, 'index']);
            Route::get('/{id}', [\App\Http\Controllers\KnowledgeController::class, 'show']);
        });
    }
}