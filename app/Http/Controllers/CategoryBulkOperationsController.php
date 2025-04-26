<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryBulkOperationsController extends Controller
{
    public function delete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:categories,id'
        ]);

        DB::transaction(function() use ($request) {
            Category::whereIn('id', $request->ids)
                ->get()
                ->each
                ->delete();
        });

        return response()->json([
            'message' => 'Selected categories deleted successfully',
            'count' => count($request->ids)
        ]);
    }

    public function move(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:categories,id',
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        $count = DB::transaction(function() use ($request) {
            return Category::whereIn('id', $request->ids)
                ->update(['parent_id' => $request->parent_id]);
        });

        return response()->json([
            'message' => 'Categories moved successfully',
            'count' => $count
        ]);
    }

    public function toggleStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:categories,id',
            'status' => 'required|boolean'
        ]);

        $count = DB::transaction(function() use ($request) {
            return Category::whereIn('id', $request->ids)
                ->update(['is_active' => $request->status]);
        });

        return response()->json([
            'message' => 'Categories status updated',
            'count' => $count
        ]);
    }
}