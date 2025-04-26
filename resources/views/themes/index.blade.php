@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div class="flex items-center space-x-4">
            <h1 class="text-3xl font-bold">Theme Management</h1>
            @if($updateCount = auth()->user()->unreadNotifications()->where('type', 'App\Notifications\ThemeUpdateAvailable')->count())
                <span class="bg-red-500 text-white text-sm px-2 py-1 rounded-full">
                    {{ $updateCount }} Update{{ $updateCount > 1 ? 's' : '' }} Available
                </span>
            @endif
        </div>
        <div class="relative inline-block text-left">
            <div>
                <button type="button" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-500 text-white hover:bg-blue-600 focus:outline-none" id="install-menu">
                    Install New Theme
                    <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            <div class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 hidden" id="install-menu-items">
                <div class="py-1" role="none">
                    <a href="{{ route('themes.marketplace.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                        Browse Marketplace
                    </a>
                    <a href="{{ route('themes.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                        Upload Theme
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($updateCount = auth()->user()->unreadNotifications()->where('type', 'App\Notifications\ThemeUpdateAvailable')->count())
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
            <div class="flex justify-between items-center">
                <div>
                    <strong>Theme Updates Available!</strong>
                    <p class="mt-1">You have {{ $updateCount }} theme update{{ $updateCount > 1 ? 's' : '' }} waiting to be installed.</p>
                </div>
                <a href="{{ route('themes.updates') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                    View Updates
                </a>
            </div>
        </div>
    @endif

    @push('scripts')
    <script>
        document.getElementById('install-menu').addEventListener('click', function() {
            const menu = document.getElementById('install-menu-items');
            menu.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('install-menu-items');
            const button = document.getElementById('install-menu');
            if (!button.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });
    </script>
    @endpush

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($themes as $theme)
            <div class="bg-white rounded-lg shadow-md overflow-hidden border @if($theme->is_active) border-green-500 @endif">
                <div class="p-4">
                    <div class="flex justify-between items-start">
                        <h2 class="text-xl font-semibold">{{ $theme->name }}</h2>
                        <div class="flex space-x-2">
                            @if($theme->is_active)
                                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Active</span>
                            @endif
                            @if($theme->hasUpdateNotification())
                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">Update Available</span>
                            @endif
                        </div>
                    </div>
                    <p class="text-gray-600 mt-2">{{ $theme->description }}</p>
                    <div class="mt-4 flex space-x-2">
                        @if(!$theme->is_active)
                            <form action="{{ route('themes.activate', $theme) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                    Activate
                                </button>
                            </form>
                            <form action="{{ route('themes.preview', $theme) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm">
                                    Preview
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('themes.show', $theme) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 py-1 rounded text-sm">
                            Details
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($activeTheme && $activeTheme->is_preview)
        <div class="mt-8 p-4 bg-yellow-100 border border-yellow-400 rounded">
            <p class="text-yellow-800">You are currently previewing a theme. Changes are not saved.</p>
            <form action="{{ route('themes.reset-preview') }}" method="POST" class="mt-2">
                @csrf
                <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm">
                    Reset Preview
                </button>
            </form>
        </div>
    @endif
</div>
@endsection
