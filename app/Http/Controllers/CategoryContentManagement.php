<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Content;
use Illuminate\Http\Request;

trait CategoryContentManagement
{
    /**
     * Reorder contents within a category
     */
    public function reorderContents(Category $category, Request $request)
    {
        $request->validate([
            'content_ids' => 'required|array',
            'content_ids.*' => 'exists:contents,id'
        ]);

        try {
            $category->contents()->sync($request->content_ids);
            
            return response()->json([
                'success' => true,
                'message' => 'Content order updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update content order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove content from a category
     */
    public function removeContent(Category $category, Request $request)
    {
        $request->validate([
            'content_id' => 'required|exists:contents,id'
        ]);

        try {
            $category->contents()->detach($request->content_id);
            
            return response()->json([
                'success' => true,
                'message' => 'Content removed from category'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove content: ' . $e->getMessage()
            ], 500);
        }
    }
}