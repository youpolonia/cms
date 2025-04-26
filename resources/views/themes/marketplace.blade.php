@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Theme Marketplace</h1>
        <div class="flex space-x-4">
            <form method="GET" action="{{ route('themes.marketplace.index') }}" class="flex space-x-4">
                <!-- Marketplace Selector -->
                <div class="relative">
                    <select name="marketplace" class="appearance-none bg-white border border-gray-300 rounded-md px-4 py-2 pr-8" onchange="this.form.submit()">
                        @foreach($availableMarketplaces as $mp)
                            <option value="{{ $mp['id'] }}" {{ $currentMarketplace == $mp['id'] ? 'selected' : '' }}>
                                {{ $mp['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Category Filter -->
                <div class="relative">
                    <select name="category" class="appearance-none bg-white border border-gray-300 rounded-md px-4 py-2 pr-8" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <option value="blog" {{ $currentCategory == 'blog' ? 'selected' : '' }}>Blog</option>
                        <option value="business" {{ $currentCategory == 'business' ? 'selected' : '' }}>Business</option>
                        <option value="portfolio" {{ $currentCategory == 'portfolio' ? 'selected' : '' }}>Portfolio</option>
                    </select>
                </div>

                <!-- Sort Options -->
                <div class="relative">
                    <select name="sort" class="appearance-none bg-white border border-gray-300 rounded-md px-4 py-2 pr-8" onchange="this.form.submit()">
                        <option value="popular" {{ $currentSort == 'popular' ? 'selected' : '' }}>Sort By: Popular</option>
                        <option value="newest" {{ $currentSort == 'newest' ? 'selected' : '' }}>Sort By: Newest</option>
                        <option value="rating" {{ $currentSort == 'rating' ? 'selected' : '' }}>Sort By: Rating</option>
                    </select>
                </div>

                <!-- Search Input -->
                <div class="relative">
                    <input type="text" name="query" value="{{ $query }}" 
                           placeholder="Search themes..." 
                           class="border border-gray-300 rounded-md px-4 py-2 pl-10 w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                    Search
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(isset($error))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ $error }}
        </div>
    @endif

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center space-x-4">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                <span class="text-lg">Loading themes...</span>
            </div>
        </div>
    </div>

    <!-- Theme Details Modal -->
    <div id="themeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-start">
                    <h2 id="modalThemeName" class="text-2xl font-bold"></h2>
                    <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <img id="modalThemeScreenshot" src="" alt="Theme Screenshot" class="w-full rounded-lg shadow">
                    </div>
                    <div>
                        <div class="flex items-center mb-2">
                            <span class="text-yellow-500">★</span>
                            <span id="modalThemeRating" class="ml-1"></span>
                            <span id="modalThemeRatingCount" class="ml-1 text-sm text-gray-500"></span>
                        </div>
                        <div id="modalThemeDownloads" class="text-sm text-gray-600 mb-4"></div>
                        <div id="modalThemeDescription" class="mb-4"></div>
                        <div class="mb-4">
                            <h3 class="font-semibold mb-2">Features</h3>
                            <div id="modalThemeTags" class="flex flex-wrap gap-2"></div>
                        </div>
                        <div class="flex space-x-3">
                            <a id="modalThemeDemoLink" href="#" target="_blank" 
                               class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                                View Demo
                            </a>
                            <form id="modalThemeInstallForm" action="#" method="POST" class="theme-install-form">
                                @csrf
                                <button type="submit" 
                                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded flex items-center">
                                    <span class="install-text">Install Theme</span>
                                    <svg class="animate-spin -ml-1 mr-1 h-4 w-4 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recently Viewed Themes -->
    <div class="mb-8 hidden" id="recentlyViewedSection">
        <h2 class="text-2xl font-bold mb-4">Recently Viewed</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" id="recentlyViewedContainer">
            <!-- Dynamically populated by JavaScript -->
        </div>
    </div>

    <!-- Featured Themes -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold mb-4">Featured Themes</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            @foreach($themes->take(4) as $theme)
                @if(($theme['downloaded'] ?? 0) > 10000 || ($theme['rating'] ?? 0) > 4.5)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden border-2 border-blue-500">
                        <div class="relative">
                            <img src="{{ $theme['screenshot_url'] ?? 'https://via.placeholder.com/600x400?text=Theme+Preview' }}" 
                                 alt="{{ $theme['name'] }} Preview" 
                                 class="w-full h-48 object-cover">
                            <div class="absolute top-2 right-2 flex flex-col space-y-1">
                                <span class="bg-blue-500 text-white text-xs px-2 py-1 rounded">
                                    Featured
                                </span>
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-semibold">{{ $theme['name'] }}</h3>
                            <div class="flex items-center mt-1">
                                <span class="text-yellow-500">★</span>
                                <span class="ml-1 text-gray-600">{{ number_format($theme['rating'] ?? 0, 1) }}</span>
                            </div>
                            <div class="mt-2">
                                <a href="#" onclick="openModal(@json($theme)); return false;" 
                                   class="text-blue-500 hover:text-blue-700 text-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- All Themes -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="themesContainer">
        @foreach($themes as $theme)
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="relative">
                    <img src="{{ $theme['screenshot_url'] ?? 'https://via.placeholder.com/600x400?text=Theme+Preview' }}" 
                         alt="{{ $theme['name'] }} Preview" 
                         class="w-full h-48 object-cover">
                    <div class="absolute top-2 right-2 flex flex-col space-y-1">
                        <span class="bg-blue-500 text-white text-xs px-2 py-1 rounded">
                            Free
                        </span>
                        @if(($theme['downloaded'] ?? 0) > 10000)
                            <span class="bg-purple-500 text-white text-xs px-2 py-1 rounded">
                                Popular
                            </span>
                        @endif
                        @if(isset($theme['is_new']) && $theme['is_new'])
                            <span class="bg-green-500 text-white text-xs px-2 py-1 rounded">
                                New
                            </span>
                        @endif
                        @if(isset($theme['update_available']) && $theme['update_available'])
                            <span class="bg-yellow-500 text-white text-xs px-2 py-1 rounded">
                                Update
                            </span>
                        @endif
                    </div>
                </div>
                <div class="p-4">
                    <div class="flex justify-between items-start">
                        <h2 class="text-xl font-semibold">{{ $theme['name'] }}</h2>
                        <div class="flex flex-col items-end">
                            <div class="flex items-center">
                                <span class="text-yellow-500">★</span>
                                <span class="ml-1 text-gray-600">{{ number_format($theme['rating'] ?? 0, 1) }}</span>
                                <span class="ml-1 text-xs text-gray-500">({{ $theme['num_ratings'] ?? 0 }})</span>
                            </div>
                            @if(isset($theme['local_rating']))
                                <div class="flex items-center mt-1">
                                    <span class="text-xs text-gray-500">Local: </span>
                                    <span class="text-yellow-500 ml-1">★</span>
                                    <span class="ml-1 text-xs text-gray-600">{{ number_format($theme['local_rating'], 1) }}</span>
                                    <span class="ml-1 text-xs text-gray-500">({{ $theme['local_rating_count'] }})</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <p class="text-gray-600 mt-2 line-clamp-2">{{ $theme['description'] ?? 'No description available' }}</p>
                    <div class="mt-4 flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                        <div>{{ number_format($theme['downloaded'] ?? 0) }} downloads</div>
                        <div class="flex items-center">
                            v{{ $theme['version'] ?? '1.0' }}
                            @if(isset($theme['update_available']) && $theme['update_available'])
                                <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs px-2 py-0.5 rounded-full">
                                    Update Available
                                </span>
                            @endif
                        </div>
                        </div>
                    <div class="flex space-x-2">
                        <a href="{{ $theme['homepage'] ?? '#' }}" target="_blank"
                           class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 py-1 rounded text-sm flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Demo
                        </a>
                        <form action="{{ route('themes.marketplace.download', $theme['id']) }}" method="POST" class="theme-install-form">
                                @csrf
                                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm flex items-center">
                                    <span class="install-text">Install</span>
                                    <svg class="animate-spin -ml-1 mr-1 h-4 w-4 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $themes->links() }}
    </div>

    @push('scripts')
    <script>
        // Recently Viewed Themes
        document.addEventListener('DOMContentLoaded', function() {
            const recentlyViewed = JSON.parse(localStorage.getItem('recentlyViewedThemes') || '[]');
            if (recentlyViewed.length > 0) {
                const container = document.getElementById('recentlyViewedContainer');
                const section = document.getElementById('recentlyViewedSection');
                
                section.classList.remove('hidden');
                
                recentlyViewed.slice(0, 4).forEach(themeId => {
                    const theme = @json($themes->items()).find(t => t.id === themeId);
                    if (theme) {
                        const card = document.createElement('div');
                        card.className = 'bg-white rounded-lg shadow-md overflow-hidden cursor-pointer hover:shadow-lg transition-shadow';
                        card.innerHTML = `
                            <div class="relative">
                                <img src="${theme.screenshot_url || 'https://via.placeholder.com/600x400?text=Theme+Preview'}" 
                                     alt="${theme.name} Preview" 
                                     class="w-full h-48 object-cover">
                            </div>
                            <div class="p-4">
                                <h3 class="text-lg font-semibold">${theme.name}</h3>
                                <div class="flex items-center mt-1">
                                    <span class="text-yellow-500">★</span>
                                    <span class="ml-1 text-gray-600">${theme.rating ? theme.rating.toFixed(1) : 'N/A'}</span>
                                </div>
                            </div>
                        `;
                        card.addEventListener('click', () => openModal(theme));
                        container.appendChild(card);
                    }
                });
            }
        });

        // Show loading indicator during searches/filters
        document.querySelector('form').addEventListener('submit', function() {
            document.getElementById('loadingIndicator').classList.remove('hidden');
        });

        // Theme modal functions
        async function openModal(theme) {
            // Track recently viewed themes
            let recentlyViewed = JSON.parse(localStorage.getItem('recentlyViewedThemes') || '[]');
            recentlyViewed = recentlyViewed.filter(id => id !== theme.id); // Remove if already exists
            recentlyViewed.unshift(theme.id); // Add to beginning
            recentlyViewed = recentlyViewed.slice(0, 10); // Keep only last 10
            localStorage.setItem('recentlyViewedThemes', JSON.stringify(recentlyViewed));

            document.getElementById('modalThemeName').textContent = theme.name;
            document.getElementById('modalThemeScreenshot').src = theme.screenshot_url || 'https://via.placeholder.com/600x400?text=Theme+Preview';
            
            // Show loading state while fetching latest details
            document.getElementById('modalThemeRating').textContent = 'Loading...';
            document.getElementById('modalThemeRatingCount').textContent = '';
            document.getElementById('modalThemeDownloads').textContent = 'Loading...';
            document.getElementById('modalThemeDescription').textContent = 'Loading...';
            document.getElementById('modalThemeDemoLink').href = theme.homepage || '#';
            document.getElementById('modalThemeInstallForm').action = `/themes/marketplace/${theme.id}/download`;

            // Clear tags
            document.getElementById('modalThemeTags').innerHTML = '';
            
            document.getElementById('themeModal').classList.remove('hidden');

            try {
                // Fetch latest theme details
                const response = await fetch(`/themes/marketplace/${theme.id}`);
                const data = await response.json();
                
                if (data.error) {
                    throw new Error(data.error);
                }

                const themeDetails = data.data;
                
                // Update modal with fresh data
                document.getElementById('modalThemeRating').textContent = themeDetails.rating ? themeDetails.rating.toFixed(1) : 'N/A';
                document.getElementById('modalThemeRatingCount').textContent = themeDetails.num_ratings ? `(${themeDetails.num_ratings})` : '';
                document.getElementById('modalThemeDownloads').textContent = `${themeDetails.downloaded ? themeDetails.downloaded.toLocaleString() : '0'} downloads`;
                document.getElementById('modalThemeDescription').textContent = themeDetails.description || 'No description available';
                
                // Update tags
                const tagsContainer = document.getElementById('modalThemeTags');
                if (themeDetails.tags) {
                    themeDetails.tags.forEach(tag => {
                        const tagElement = document.createElement('span');
                        tagElement.className = 'bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded';
                        tagElement.textContent = tag;
                        tagsContainer.appendChild(tagElement);
                    });
                }

                // Show update available badge if needed
                if (themeDetails.update_available) {
                    const updateBadge = document.createElement('span');
                    updateBadge.className = 'ml-2 bg-yellow-100 text-yellow-800 text-xs px-2 py-0.5 rounded-full';
                    updateBadge.textContent = 'Update Available';
                    document.getElementById('modalThemeRating').parentNode.appendChild(updateBadge);
                }

            } catch (error) {
                console.error('Failed to load theme details:', error);
                document.getElementById('modalThemeDescription').textContent = `Error loading theme details: ${error.message}`;
            }
        }

        function closeModal() {
            document.getElementById('themeModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('themeModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Handle theme card clicks
        document.getElementById('themesContainer').addEventListener('click', function(e) {
            const card = e.target.closest('.bg-white.rounded-lg.shadow-md');
            if (card) {
                const themeId = card.dataset.themeId;
                const theme = @json($themes->items()).find(t => t.id === themeId);
                if (theme) {
                    openModal(theme);
                }
            }
        });

        // Handle theme installation forms
        document.querySelectorAll('.theme-install-form').forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const button = this.querySelector('button[type="submit"]');
                const spinner = button.querySelector('svg');
                const text = button.querySelector('.install-text');
                
                // Show loading state
                button.disabled = true;
                spinner.classList.remove('hidden');
                text.textContent = 'Installing...';
                
                try {
                    const response = await fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(Object.fromEntries(new FormData(this)))
                    });
                    
                    const data = await response.json();
                    
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    // Show success
                    text.textContent = 'Installed!';
                    setTimeout(() => {
                        text.textContent = 'Install';
                        spinner.classList.add('hidden');
                        button.disabled = false;
                    }, 2000);
                    
                    // Reload page to show updated theme state
                    setTimeout(() => window.location.reload(), 2500);
                    
                } catch (error) {
                    console.error('Installation failed:', error);
                    text.textContent = 'Error - Retry';
                    spinner.classList.add('hidden');
                    button.disabled = false;
                }
            });
        });
    </script>
    @endpush
</div>
@endsection
