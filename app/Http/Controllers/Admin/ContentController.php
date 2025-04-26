<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContentController extends Controller
{
    public function index()
    {
        $contents = Content::with(['user', 'categories'])
            ->latest()
            ->paginate(20);

        return view('admin.content.index', compact('contents'));
    }

    public function create()
    {
        $categories = Category::all();
        $contentTypes = ['page', 'post', 'article'];
        return view('admin.content.create', compact('categories', 'contentTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'content_type' => 'required|in:page,post,article',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:160',
            'seo_keywords' => 'nullable|array',
        ]);

        $content = Content::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'slug' => Str::slug($validated['title']),
            'content_type' => $validated['content_type'],
            'seo_title' => $validated['seo_title'],
            'seo_description' => $validated['seo_description'],
            'seo_keywords' => $validated['seo_keywords'],
            'user_id' => auth()->id()
        ]);

        if (isset($validated['categories'])) {
            $content->categories()->sync($validated['categories']);
        }

        return redirect()->route('admin.content.index')
            ->with('success', 'Content created successfully');
    }

    public function show(Content $content)
    {
        return view('admin.content.show', compact('content'));
    }

    public function edit(Content $content)
    {
        $categories = Category::all();
        $contentTypes = ['page', 'post', 'article'];
        return view('admin.content.edit', compact('content', 'categories', 'contentTypes'));
    }

    public function update(Request $request, Content $content)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'content_type' => 'required|in:page,post,article',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:160',
            'seo_keywords' => 'nullable|array',
        ]);

        $content->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'slug' => Str::slug($validated['title']),
            'content_type' => $validated['content_type'],
            'seo_title' => $validated['seo_title'],
            'seo_description' => $validated['seo_description'],
            'seo_keywords' => $validated['seo_keywords']
        ]);

        if (isset($validated['categories'])) {
            $content->categories()->sync($validated['categories']);
        }

        return redirect()->route('admin.content.index')
            ->with('success', 'Content updated successfully');
    }

    public function destroy(Content $content)
    {
        $content->delete();
        return redirect()->route('admin.content.index')
            ->with('success', 'Content moved to trash');
    }
}