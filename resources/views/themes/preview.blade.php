@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-4">Theme Preview: {{ $theme->name }}</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="border rounded-lg p-4">
                <h2 class="text-xl font-semibold mb-2">Previewing Theme</h2>
                <div class="space-y-2">
                    <p><span class="font-medium">Name:</span> {{ $theme->name }}</p>
                    <p><span class="font-medium">Version:</span> {{ $theme->current_version }}</p>
                    <p><span class="font-medium">Author:</span> {{ $theme->author }}</p>
                </div>
            </div>

            <div class="border rounded-lg p-4">
                <h2 class="text-xl font-semibold mb-2">Original Theme</h2>
                <div class="space-y-2">
                    <p><span class="font-medium">Name:</span> {{ $originalTheme->name }}</p>
                    <p><span class="font-medium">Version:</span> {{ $originalTheme->current_version }}</p>
                    <p><span class="font-medium">Author:</span> {{ $originalTheme->author }}</p>
                </div>
            </div>
        </div>

        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-blue-700">
                    Preview will automatically expire in {{ $previewTimeLeft }} minutes
                </p>
            </div>
        </div>

        <div class="mt-6 flex space-x-4">
            <a href="{{ route('themes.index') }}" 
               class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg transition">
                Back to Themes
            </a>
            <form action="{{ route('themes.preview.reset') }}" method="POST">
                @csrf
                <button type="submit" 
                        class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition">
                    End Preview Now
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
