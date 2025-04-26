@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $theme->name }} Changelog
                </h1>
                <a href="{{ route('themes.show', $theme) }}" 
                   class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                    ← Back to Theme
                </a>
            </div>

            <div class="prose dark:prose-invert max-w-none">
                @if($theme->changelog)
                    {!! \Illuminate\Support\Str::markdown($theme->changelog) !!}
                @else
                    <p class="text-gray-500 dark:text-gray-400">No changelog available for this theme.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
