<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PageBuilderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function generateContent(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string'
        ]);

        // TODO: Implement AI content generation
        return response()->json([
            'content' => 'Generated content based on: ' . $request->prompt
        ]);
    }

    public function suggestBlocks(Request $request)
    {
        $request->validate([
            'currentBlocks' => 'required|array'
        ]);

        // TODO: Implement block suggestion logic
        return response()->json([
            'suggestions' => [
                ['type' => 'text', 'content' => 'Suggested text block'],
                ['type' => 'image', 'content' => 'Suggested image block']
            ]
        ]);
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048'
        ]);

        $path = $request->file('image')->store('page-builder/images');
        
        return response()->json([
            'url' => asset('storage/' . $path)
        ]);
    }
}
