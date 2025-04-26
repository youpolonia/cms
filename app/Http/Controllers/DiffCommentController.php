<?php

namespace App\Http\Controllers;

use App\Models\DiffComment;
use App\Events\CommentCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiffCommentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content_id' => 'nullable|exists:contents,id',
            'version1_id' => 'nullable|exists:content_versions,id',
            'version2_id' => 'nullable|exists:content_versions,id',
            'content1_hash' => 'nullable|string|max:64',
            'content2_hash' => 'nullable|string|max:64',
            'comment' => 'required|string|max:2000',
            'diff_range' => 'required|array',
            'diff_range.start_line' => 'required|integer',
            'diff_range.end_line' => 'required|integer',
            'diff_range.start_offset' => 'nullable|integer',
            'diff_range.end_offset' => 'nullable|integer'
        ]);

        $comment = DiffComment::create([
            ...$validated,
            'user_id' => Auth::id()
        ]);

        event(new CommentCreated($comment));

        return response()->json($comment, 201);
    }

    public function index(Request $request)
    {
        $query = DiffComment::with(['user', 'content', 'version1', 'version2']);

        if ($request->has('content_id')) {
            $query->where('content_id', $request->content_id);
        }

        if ($request->has('version1_id')) {
            $query->where('version1_id', $request->version1_id);
        }

        if ($request->has('version2_id')) {
            $query->where('version2_id', $request->version2_id);
        }

        if ($request->has('content_hash')) {
            $query->where(function($q) use ($request) {
                $q->where('content1_hash', $request->content_hash)
                  ->orWhere('content2_hash', $request->content_hash);
            });
        }

        return response()->json($query->latest()->get());
    }

    public function update(Request $request, DiffComment $comment)
    {
        $this->authorize('update', $comment);

        $validated = $request->validate([
            'comment' => 'required|string|max:2000'
        ]);

        $comment->update($validated);

        return response()->json($comment);
    }
    public function destroy(DiffComment $comment)
    {
        $this->authorize('delete', $comment);
        
        $comment->delete();

        return response()->json(['message' => 'Comment deleted']);
    }
}