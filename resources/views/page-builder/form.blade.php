@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">
        @isset($page)
            Edit Page: {{ $page->title }}
        @else
            Create New Page
        @endisset
    </h1>

    <form method="POST" 
          action="{{ isset($page) ? route('page-builder.update', $page) : route('page-builder.store') }}"
          class="bg-white rounded-lg shadow p-6">
        @csrf
        @isset($page)
            @method('PUT')
        @endisset

        <div class="mb-4">
            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Page Title</label>
            <input type="text" name="title" id="title" 
                   value="{{ old('title', $page->title ?? '') }}"
                   class="form-input w-full" required>
            @error('title')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <div class="flex justify-between items-center mb-1">
                <label class="block text-sm font-medium text-gray-700">Page Blocks</label>
                <button type="button" id="ai-suggest-btn" class="text-sm text-blue-600 hover:text-blue-800">
                    AI Suggestions
                </button>
            </div>
            
            <div class="grid grid-cols-4 gap-4">
                <div class="col-span-3">
                    <div id="page-builder-editor" class="min-h-[400px] border border-gray-300 rounded p-4">
                        <!-- Blocks editor will be implemented with JavaScript -->
                    </div>
                    <input type="hidden" name="blocks" id="blocks-input"
                           value="{{ old('blocks', isset($page) ? json_encode($page->blocks) : '[]') }}">
                    @error('blocks')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="col-span-1">
                    <div class="bg-gray-50 p-4 rounded border border-gray-200">
                        <h3 class="font-medium text-gray-700 mb-2">AI Assistant</h3>
                        <div class="space-y-3">
                            <button type="button" id="generate-content-btn" class="w-full btn btn-sm btn-outline">
                                Generate Content
                            </button>
                            <button type="button" id="suggest-blocks-btn" class="w-full btn btn-sm btn-outline">
                                Suggest Blocks
                            </button>
                        </div>
                        <div id="ai-output" class="mt-4 text-sm"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <a href="{{ route('page-builder.index') }}" class="btn btn-secondary mr-2">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                @isset($page)
                    Update Page
                @else
                    Create Page
                @endisset
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
@vite(['resources/js/page-builder.js'])
@endsection