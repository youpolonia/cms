<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index()
    {
        return Page::with('blocks')->get();
    }

    public function show(Page $page)
    {
        return $page->load('blocks');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:pages',
            'user_id' => 'required|exists:users,id'
        ]);

        return Page::create($validated);
    }

    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|unique:pages,slug,'.$page->id,
            'metadata' => 'sometimes|array'
        ]);

        $page->update($validated);
        return $page;
    }

    public function destroy(Page $page)
    {
        $page->delete();
        return response()->noContent();
    }
}