<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('contents')
            ->orderBy('name')
            ->paginate(20);
            
        return view('categories.index', compact('categories'));
    }
    
    public function create()
    {
        return view('categories.create', [
            'category' => new Category()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'order' => 'integer',
            'is_active' => 'boolean'
        ]);

        $category = Category::create($validated);
        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully');
    }

    public function show(Category $category)
    {
        return $category->load(['parent', 'children', 'contents']);
    }
    
    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('categories')->ignore($category->id)
            ],
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'order' => 'sometimes|integer',
            'is_active' => 'sometimes|boolean'
        ]);

        $category->update($validated);
        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully');
    }
    public function content(Category $category)
    {
        $contents = \App\Models\Content::orderBy('title')->get();
        return view('categories.assign_content', [
            'category' => $category,
            'contents' => $contents
        ]);
    }

    public function storeContent(Request $request, Category $category)
    {
        $request->validate([
            'contents' => 'nullable|array',
            'contents.*' => 'exists:contents,id',
            'order' => 'nullable|array',
            'order.*' => 'integer'
        ]);

        $syncData = [];
        if ($request->contents) {
            foreach ($request->contents as $contentId) {
                $syncData[$contentId] = ['order' => $request->order[$contentId] ?? 0];
            }
        }

        $category->contents()->sync($syncData);

        return redirect()->route('categories.index')
            ->with('success', 'Content assignments updated successfully');
    }
}
