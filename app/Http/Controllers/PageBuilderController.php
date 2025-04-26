<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PageBuilderController extends Controller
{
    public function save(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|array',
            'is_template' => 'boolean'
        ]);

        $path = $validated['is_template'] 
            ? 'templates/'.$validated['name'].'.json'
            : 'pages/'.$validated['name'].'.json';

        Storage::disk('local')->put($path, json_encode($validated['content']));

        return response()->json([
            'success' => true,
            'message' => 'Saved successfully'
        ]);
    }

    public function load(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'is_template' => 'boolean'
        ]);

        $path = $validated['is_template']
            ? 'templates/'.$validated['name'].'.json'
            : 'pages/'.$validated['name'].'.json';

        if (!Storage::disk('local')->exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'content' => json_decode(Storage::disk('local')->get($path), true)
        ]);
    }

    public function listTemplates()
    {
        $files = Storage::disk('local')->files('templates');
        
        return response()->json([
            'success' => true,
            'templates' => array_map(function($file) {
                return pathinfo($file, PATHINFO_FILENAME);
            }, $files)
        ]);
    }
}