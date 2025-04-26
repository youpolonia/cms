<?php

namespace App\Http\Controllers;

use App\Models\BlockTemplate;
use Illuminate\Http\Request;

class BlockTemplateController extends Controller
{
    public function index()
    {
        return BlockTemplate::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:block_templates',
            'blocks' => 'required|array',
            'blocks.*.type' => 'required|string|in:text,image,video',
            'blocks.*.content' => 'required|array'
        ]);

        $template = BlockTemplate::create([
            'name' => $validated['name'],
            'blocks' => $validated['blocks']
        ]);

        return response()->json($template, 201);
    }

    public function show(BlockTemplate $template)
    {
        return $template;
    }

    public function update(Request $request, BlockTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:block_templates,name,'.$template->id,
            'blocks' => 'required|array',
            'blocks.*.type' => 'required|string|in:text,image,video',
            'blocks.*.content' => 'required|array'
        ]);

        $template->update([
            'name' => $validated['name'],
            'blocks' => $validated['blocks']
        ]);

        return $template;
    }

    public function destroy(BlockTemplate $template)
    {
        $template->delete();
        return response()->noContent();
    }
}
