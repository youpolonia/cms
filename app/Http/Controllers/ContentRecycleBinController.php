<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;

class ContentRecycleBinController extends Controller
{
    public function index()
    {
        $this->authorize('viewAnyTrashed', Content::class);
        
        $trashedContents = Content::onlyTrashed()
            ->with(['user', 'categories'])
            ->latest('deleted_at')
            ->paginate(15);

        return view('content.recycle-bin.index', compact('trashedContents'));
    }

    public function restore($id)
    {
        $content = Content::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $content);
        $content->restore();

        return redirect()->route('content.recycle-bin.index')
            ->with('success', 'Content restored successfully');
    }

    public function forceDelete($id)
    {
        $content = Content::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $content);
        $content->forceDelete();

        return redirect()->route('content.recycle-bin.index')
            ->with('success', 'Content permanently deleted');
    }

    public function empty()
    {
        $this->authorize('emptyTrash', Content::class);
        Content::onlyTrashed()->forceDelete();

        return redirect()->route('content.recycle-bin.index')
            ->with('success', 'Recycle bin emptied');
    }
}