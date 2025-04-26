<?php

namespace App\Http\Controllers;

use App\Models\MediaCollection;
use Illuminate\Http\Request;

class MediaCollectionController extends Controller
{
    public function index()
    {
        return view('media.collections.index', [
            'collections' => MediaCollection::withCount('items')
                ->latest()
                ->paginate(25)
        ]);
    }

    public function create()
    {
        return view('media.collections.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:media_collections'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_private' => ['boolean']
        ]);

        MediaCollection::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_private' => $validated['is_private'] ?? false,
            'creator_id' => auth()->id()
        ]);

        return redirect()->route('media.collections.index')
            ->with('success', 'Collection created successfully');
    }

    public function show(MediaCollection $collection)
    {
        return view('media.collections.show', [
            'collection' => $collection->load(['items.media', 'creator'])
        ]);
    }

    public function edit(MediaCollection $collection)
    {
        return view('media.collections.edit', compact('collection'));
    }

    public function update(Request $request, MediaCollection $collection)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:media_collections,name,'.$collection->id],
            'description' => ['nullable', 'string', 'max:500'],
            'is_private' => ['boolean']
        ]);

        $collection->update($validated);

        return redirect()->route('media.collections.show', $collection)
            ->with('success', 'Collection updated successfully');
    }

    public function destroy(MediaCollection $collection)
    {
        $collection->delete();

        return redirect()->route('media.collections.index')
            ->with('success', 'Collection deleted successfully');
    }
}
