<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="/">
                        <span class="block h-9 w-auto text-gray-800 font-bold text-xl">CMS</span>
                    </a>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="hidden sm:flex sm:items-center sm:ml-6 space-x-4">
                @auth
                    <notification-bell class="mr-4"></notification-bell>
                    
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center text-sm font-medium text-gray-700 hover:text-gray-900">
                            Admin
                            <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div x-show="open" @click.away="open = false"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                            <div class="py-1">
                                <a href="{{ route('autopilot.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Dashboard
                                </a>
                                <a href="{{ route('analytics.ai-usage') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    AI Analytics
                                </a>
                                <a href="{{ route('content.list') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Content List
                                </a>
                                <a href="{{ route('moderation.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Moderation Queue
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-gray-700 hover:text-gray-900">Log in</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

@can('viewAny', \App\Models\Content::class)
    <x-nav-link :href="route('content.recycle-bin.index')" :active="request()->routeIs('content.recycle-bin.*')">
        {{ __('Recycle Bin') }}
    </x-nav-link>
@endcan

@can('viewAny', \App\Models\Content::class)
    <x-nav-link :href="route('contents.index')" :active="request()->routeIs('contents.*')">
        {{ __('Content Management') }}
    </x-nav-link>
@endcan
