@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">{{ $theme->name }}</h1>
        <div class="flex space-x-2">
            <a href="{{ route('themes.index') }}" class="btn btn-secondary">
                Back to Themes
            </a>
            <a href="{{ route('themes.version-history', $theme) }}" class="btn btn-primary">
                Version History
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500">Current Version</p>
                    <p class="text-lg font-medium">{{ $theme->current_version }}</p>
                </div>
                
                @if($theme->hasUpdateAvailable())
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    Update available: {{ $theme->update_available_version }}
                                </p>
                                <form action="{{ route('themes.update', $theme) }}" method="POST" class="mt-2">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        Update Now
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <form action="{{ route('themes.check-updates', $theme) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-secondary">
                            Check for Updates
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Rest of existing theme show content -->
</div>
@endsection
