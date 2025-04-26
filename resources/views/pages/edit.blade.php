@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Edit Page: {{ $page->title }}</h1>

    <form action="{{ route('pages.update', $page) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-lg shadow overflow-hidden p-6 mb-6">
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" name="title" id="title" required value="{{ old('title', $page->title) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                    <input type="text" name="slug" id="slug" required value="{{ old('slug', $page->slug) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="layout" class="block text-sm font-medium text-gray-700">Layout</label>
                    <select name="layout" id="layout" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="default" {{ $page->layout === 'default' ? 'selected' : '' }}>Default</option>
                        <option value="full-width" {{ $page->layout === 'full-width' ? 'selected' : '' }}>Full Width</option>
                        <option value="sidebar-left" {{ $page->layout === 'sidebar-left' ? 'selected' : '' }}>Sidebar Left</option>
                        <option value="sidebar-right" {{ $page->layout === 'sidebar-right' ? 'selected' : '' }}>Sidebar Right</option>
                    </select>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_published" id="is_published" value="1" {{ $page->is_published ? 'checked' : '' }}
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="is_published" class="ml-2 block text-sm text-gray-700">Published</label>
                </div>

                <div>
                    <label for="published_at" class="block text-sm font-medium text-gray-700">Publish Date</label>
                    <input type="datetime-local" name="published_at" id="published_at" 
                        value="{{ old('published_at', optional($page->published_at)->format('Y-m-d\TH:i') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden p-6 mb-6">
            <h2 class="text-lg font-medium mb-4">SEO Settings</h2>
            
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="meta_title" class="block text-sm font-medium text-gray-700">Meta Title</label>
                    <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title', $page->meta_title) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="meta_description" class="block text-sm font-medium text-gray-700">Meta Description</label>
                    <textarea name="meta_description" id="meta_description" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('meta_description', $page->meta_description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn btn-primary mr-2">
                Update Page
            </button>
            <a href="{{ route('pages.index') }}" class="btn btn-secondary">
                Cancel
            </a>
        </div>
    </form>

    <div class="mt-8">
        <h2 class="text-xl font-semibold mb-4">Page Blocks</h2>
        @include('blocks._list', ['blocks' => $page->blocks])
    </div>
</div>
@endsection