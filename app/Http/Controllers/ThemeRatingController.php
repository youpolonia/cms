<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use App\Models\ThemeRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThemeRatingController extends Controller
{
    public function store(Request $request, Theme $theme)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
            'marketplace_id' => 'nullable|string',
            'marketplace_source' => 'nullable|string|required_with:marketplace_id'
        ]);

        $data = array_merge($validated, ['user_id' => Auth::id()]);
        
        $rating = $theme->ratings()->updateOrCreate(
            [
                'user_id' => Auth::id(),
                'marketplace_id' => $validated['marketplace_id'] ?? null
            ],
            $data
        );

        return response()->json([
            'rating' => $rating,
            'average_rating' => $theme->averageRating(),
            'rating_count' => $theme->ratingCount()
        ], 201);
    }

    public function show(Theme $theme)
    {
        return response()->json([
            'ratings' => $theme->ratings()
                ->with('user:id,name')
                ->latest()
                ->paginate(10),
            'average_rating' => $theme->averageRating(),
            'rating_count' => $theme->ratingCount(),
            'rating_distribution' => $theme->ratingDistribution(),
            'user_rating' => Auth::check() 
                ? $theme->ratings()->where('user_id', Auth::id())->first()
                : null
        ]);
    }

    public function stats(Theme $theme)
    {
        return response()->json([
            'average_rating' => $theme->averageRating(),
            'rating_count' => $theme->ratingCount(),
            'rating_distribution' => $theme->ratingDistribution(),
            'recent_reviews' => $theme->ratings()
                ->with('user:id,name')
                ->whereNotNull('review')
                ->latest()
                ->limit(3)
                ->get()
        ]);
    }

    public function userRating(Theme $theme)
    {
        if (!Auth::check()) {
            return response()->json(null, 404);
        }

        $rating = $theme->ratings()
            ->where('user_id', Auth::id())
            ->first();

        return response()->json($rating);
    }

    public function destroy(Theme $theme)
    {
        $theme->ratings()
            ->where('user_id', Auth::id())
            ->delete();

        return response()->json([
            'average_rating' => $theme->averageRating(),
            'rating_count' => $theme->ratingCount()
        ]);
    }
}
