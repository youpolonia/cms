<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\BlockVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockVersionController extends Controller
{
    public function index(Block $block)
    {
        return response()->json([
            'versions' => $block->versions()
                ->orderBy('created_at', 'desc')
                ->get()
        ]);
    }

    public function store(Request $request, Block $block)
    {
        $version = BlockVersion::create([
            'block_id' => $block->id,
            'user_id' => Auth::id(),
            'content' => $request->content,
            'changes' => $request->changes
        ]);

        return response()->json($version, 201);
    }

    public function restore(BlockVersion $version)
    {
        $block = $version->block;
        $block->update(['content' => $version->content]);

        return response()->json([
            'message' => 'Version restored successfully',
            'block' => $block
        ]);
    }

    public function compare(BlockVersion $version1, BlockVersion $version2)
    {
        return response()->json([
            'version1' => $version1,
            'version2' => $version2,
            'diff' => $this->getDiff($version1->content, $version2->content)
        ]);
    }

    private function getDiff(array $old, array $new): array
    {
        // Implement diff algorithm to compare versions
        return [
            'added' => array_diff_assoc($new, $old),
            'removed' => array_diff_assoc($old, $new),
            'changed' => []
        ];
    }
}