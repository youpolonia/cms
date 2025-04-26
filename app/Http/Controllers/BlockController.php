<?php

namespace App\Http\Controllers;

use App\Models\Block;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BlockController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Block::class);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|array',
            'is_template' => 'sometimes|boolean'
        ]);

        $block = Block::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'content' => json_encode($validated['content']),
            'is_template' => $validated['is_template'] ?? false
        ]);

        return response()->json($block, 201);
    }

    public function index()
    {
        return Block::where('user_id', auth()->id())
            ->orWhere('is_template', true)
            ->get()
            ->map(function($block) {
                return [
                    'id' => $block->id,
                    'name' => $block->name,
                    'content' => json_decode($block->content, true),
                    'is_template' => $block->is_template,
                    'created_at' => $block->created_at
                ];
            });
    }

    public function show(Block $block)
    {
        $this->authorize('view', $block);
        
        return [
            'id' => $block->id,
            'name' => $block->name,
            'content' => json_decode($block->content, true),
            'is_template' => $block->is_template,
            'created_at' => $block->created_at
        ];
    }

    public function update(Request $request, Block $block)
    {
        $this->authorize('update', $block);
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'content' => 'sometimes|array',
            'is_template' => 'sometimes|boolean'
        ]);

        $block->update([
            'name' => $validated['name'] ?? $block->name,
            'content' => isset($validated['content']) ? json_encode($validated['content']) : $block->content,
            'is_template' => $validated['is_template'] ?? $block->is_template
        ]);

        return response()->json([
            'id' => $block->id,
            'name' => $block->name,
            'content' => json_decode($block->content, true),
            'is_template' => $block->is_template,
            'updated_at' => $block->updated_at
        ]);
    }

    public function destroy(Block $block)
    {
        $this->authorize('delete', $block);
        $block->delete();
        return response()->noContent();
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'page_id' => 'required|exists:pages,id',
            'blocks' => 'required|array',
            'blocks.*.id' => 'required|exists:blocks,id',
            'blocks.*.order' => 'required|integer'
        ]);

        DB::transaction(function() use ($validated) {
            foreach ($validated['blocks'] as $block) {
                Block::where('id', $block['id'])
                    ->update(['order' => $block['order']]);
            }
        });

        return response()->json(['message' => 'Blocks reordered successfully']);
    }

    public function duplicate(Block $block)
    {
        $this->authorize('view', $block);
        
        $newBlock = $block->replicate();
        $newBlock->order = Block::where('page_id', $block->page_id)->max('order') + 1;
        $newBlock->save();

        return response()->json([
            'id' => $newBlock->id,
            'name' => $newBlock->name,
            'content' => json_decode($newBlock->content, true),
            'is_template' => $newBlock->is_template,
            'created_at' => $newBlock->created_at
        ]);
    }
}