<?php

namespace App\Http\Controllers\Traits;

use App\Models\Category;
use App\Models\Content;
use Illuminate\Http\Request;

trait CategoryContentManagement
{
    /**
     * Add content to a category with optional order
     */
    public function addContent(Category $category, Request $request)
    {
        $request->validate([
            'content_id' => 'required|exists:contents,id',
            'order' => 'nullable|integer|min:0'
        ]);

        try {
            $category->addContent(
                Content::find($request->content_id),
                $request->order
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Content added to category'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add content: ' . $e->getMessage()
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
            $category->removeContent(Content::find($request->content_id));
            
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

    /**
     * Reorder contents within a category with pivot values
     */
    public function reorderContents(Category $category, Request $request)
    {
        $request->validate([
            'content_ids' => 'required|array',
            'content_ids.*' => 'exists:contents,id'
        ]);

        try {
            $category->reorderContents($request->content_ids);
            
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
     * Bulk add/remove contents from a category
     */
    public function bulkManageContents(Category $category, Request $request)
    {
        $request->validate([
            'add' => 'nullable|array',
            'add.*' => 'exists:contents,id',
            'remove' => 'nullable|array',
            'remove.*' => 'exists:contents,id'
        ]);

        DB::beginTransaction();
        try {
            if ($request->add) {
                foreach ($request->add as $contentId) {
                    $category->addContent(Content::find($contentId));
                }
            }

            if ($request->remove) {
                $category->contents()->detach($request->remove);
            }

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Bulk content management completed'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to manage contents: ' . $e->getMessage()
            ], 500);
        }
    }
}