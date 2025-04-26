@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">{{ $page->title }}</h1>
        <div class="flex space-x-2">
            <a href="{{ route('pages.index') }}" class="btn btn-secondary">
                Back to Pages
            </a>
            @can('update', $page)
            <a href="{{ route('pages.edit', $page) }}" class="btn btn-primary">
                Edit Page
            </a>
            @endcan
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden mb-8">
        <div class="p-6">
            <div class="mb-6">
                <div class="text-sm text-gray-500 mb-1">Slug</div>
                <div class="font-medium">{{ $page->slug }}</div>
            </div>

            <div class="mb-6">
                <div class="text-sm text-gray-500 mb-1">Status</div>
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $page->is_published ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $page->is_published ? 'Published' : 'Draft' }}
                </span>
                @if($page->is_published && $page->published_at)
                <div class="text-sm text-gray-500 mt-1">
                    Published on {{ $page->published_at->format('M j, Y \a\t g:i A') }}
                </div>
                @endif
            </div>

            <div class="mb-6">
                <div class="text-sm text-gray-500 mb-1">Layout</div>
                <div class="font-medium">{{ ucfirst(str_replace('-', ' ', $page->layout)) }}</div>
            </div>

            @if($page->meta_title || $page->meta_description)
            <div class="mb-6">
                <h2 class="text-lg font-medium mb-2">SEO Metadata</h2>
                @if($page->meta_title)
                <div class="mb-2">
                    <div class="text-sm text-gray-500 mb-1">Meta Title</div>
                    <div>{{ $page->meta_title }}</div>
                </div>
                @endif
                @if($page->meta_description)
                <div>
                    <div class="text-sm text-gray-500 mb-1">Meta Description</div>
                    <div>{{ $page->meta_description }}</div>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium">Page Content</h2>
        </div>
        <div class="p-6">
            @if($page->blocks->isEmpty())
            <div class="text-gray-500">No blocks added yet</div>
            @else
            <div class="space-y-6">
                @foreach($page->blocks->sortBy('position') as $block)
                <div class="block-content">
                    @includeIf("blocks.{$block->type}")
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection