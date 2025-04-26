<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ContentController extends Controller
{
    public function index()
    {
        return Content::with(['user', 'categories'])
            ->latest()
            ->paginate(15);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'content_type' => ['required', Rule::in(['page', 'post', 'custom'])],
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at'
        ]);

        $content = Content::create([
            ...$data,
            'slug' => Str::slug($data['title']),
            'user_id' => auth()->id()
        ]);

        if ($request->has('categories')) {
            $content->categories()->sync($request->categories);
        }

        return $content->load('categories');
    }

    public function show(Content $content)
    {
        return $content->load(['user', 'categories']);
    }

    public function update(Request $request, Content $content)
    {
        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'categories' => 'sometimes|array',
            'categories.*' => 'exists:categories,id',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at'
        ]);

        if ($request->has('title')) {
            $data['slug'] = Str::slug($data['title']);
        }

        $content->update($data);

        if ($request->has('categories')) {
            $content->categories()->sync($request->categories);
        }

        return $content->fresh()->load('categories');
    }

    public function destroy(Content $content)
    {
        $content->delete();
        return response()->noContent();
    }

    public function getCategories(Content $content)
    {
        return $content->categories()->orderByPivot('order')->get();
    }

    public function syncCategories(Request $request, Content $content)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id'
        ]);

        $content->categories()->sync($request->categories);

        return $this->getCategories($content);
    }

    public function schedulePublication(Request $request, Content $content)
    {
        $data = $request->validate([
            'published_at' => 'required|date',
            'is_published' => 'sometimes|boolean'
        ]);

        $content->update([
            'published_at' => $data['published_at'],
            'is_published' => $data['is_published'] ?? false
        ]);

        return $content;
    }

    public function scheduleExpiration(Request $request, Content $content)
    {
        $data = $request->validate([
            'expires_at' => 'required|date',
            'is_published' => 'sometimes|boolean'
        ]);

        $content->update([
            'expires_at' => $data['expires_at'],
            'is_published' => $data['is_published'] ?? true
        ]);

        return $content;
    }

    public function getScheduledContent(Request $request)
    {
        return Content::query()
            ->whereNotNull('published_at')
            ->orWhereNotNull('expires_at')
            ->with(['user', 'categories'])
            ->latest()
            ->paginate(15);
    }
}
