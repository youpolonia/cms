<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BlocksController extends Controller
{
    /**
     * Get fields for a specific block type
     */
    public function fields($type)
    {
        $validTypes = ['text', 'image', 'video', 'quote'];
        
        if (!in_array($type, $validTypes)) {
            abort(404);
        }

        return view("blocks.fields.{$type}", [
            'content' => request()->old('content', [])
        ]);
    }

    /**
     * Store a newly created block
     */
    public function store(Request $request, $page)
    {
        $validated = $request->validate([
            'type' => 'required|in:text,image,video,quote',
            'content' => 'required|array'
        ]);

        // Additional type-specific validation
        switch ($validated['type']) {
            case 'text':
                $request->validate([
                    'content.text' => 'required|string',
                    'content.alignment' => 'sometimes|in:left,center,right',
                    'content.text_size' => 'sometimes|in:base,lg,xl',
                    'content.text_color' => 'sometimes|string',
                    'content.has_background' => 'sometimes|boolean',
                    'content.background_color' => 'required_if:content.has_background,true|string'
                ]);
                break;
            case 'image':
                $request->validate([
                    'content.image' => 'required_without:content.image_url|image|max:2048',
                    'content.image_url' => 'required_without:content.image|url',
                    'content.alt_text' => 'required|string|max:255',
                    'content.alignment' => 'sometimes|in:left,center,right',
                    'content.width' => 'sometimes|in:full,half,third',
                    'content.has_border' => 'sometimes|in:none,thin,thick',
                    'content.is_rounded' => 'sometimes|boolean',
                    'content.is_shadowed' => 'sometimes|boolean'
                ]);
                break;
            case 'video':
                $request->validate([
                    'content.embed_url' => 'required|url',
                    'content.alignment' => 'sometimes|in:left,center,right',
                    'content.width' => 'sometimes|in:full,half,third',
                    'content.autoplay' => 'sometimes|boolean',
                    'content.show_controls' => 'sometimes|boolean',
                    'content.loop' => 'sometimes|boolean',
                    'content.caption' => 'sometimes|string|max:255'
                ]);
                break;
            case 'quote':
                $request->validate([
                    'content.quote_text' => 'required|string',
                    'content.author' => 'sometimes|string|max:255',
                    'content.source' => 'sometimes|string|max:255',
                    'content.alignment' => 'sometimes|in:left,center,right',
                    'content.text_size' => 'sometimes|in:lg,xl,2xl',
                    'content.text_style' => 'sometimes|in:normal,italic,bold',
                    'content.show_quotes' => 'sometimes|boolean',
                    'content.has_border' => 'sometimes|boolean'
                ]);
                break;
        }

        // Create and save the block
        $block = $page->blocks()->create([
            'type' => $validated['type'],
            'content' => $validated['content']
        ]);

        return redirect()->route('pages.edit', $page)
            ->with('success', 'Block added successfully');
    }

    /**
     * Update an existing block
     */
    public function update(Request $request, $page, $block)
    {
        $validated = $request->validate([
            'type' => 'required|in:text,image,video,quote',
            'content' => 'required|array'
        ]);

        // Type-specific validation (same as store method)
        // ...

        $block->update([
            'type' => $validated['type'],
            'content' => $validated['content']
        ]);

        return redirect()->route('pages.edit', $page)
            ->with('success', 'Block updated successfully');
    }

    /**
     * Reorder blocks
     */
    public function reorder(Request $request, $page)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:blocks,id'
        ]);

        foreach ($request->order as $position => $id) {
            $page->blocks()->where('id', $id)->update(['position' => $position]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Delete a block
     */
    public function destroy($page, $block)
    {
        $block->delete();

        return redirect()->route('pages.edit', $page)
            ->with('success', 'Block deleted successfully');
    }
}