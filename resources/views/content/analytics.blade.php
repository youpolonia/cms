@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Version Comparison Analytics</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Comparison Trends Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Comparison Frequency Trends</h2>
            <div class="h-64">
                <canvas id="trendsChart"></canvas>
            </div>
        </div>

        <!-- Cache Performance -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Cache Performance</h2>
            <div class="h-64">
                <canvas id="cacheChart"></canvas>
            </div>
            <div class="mt-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Hit Rate: <span id="cacheHitRate"></span></p>
                        <p class="text-sm text-gray-600">Cache Size: <span id="cacheSize"></span></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Items Cached: <span id="cacheItemsCount"></span></p>
                        <p class="text-sm text-gray-600">Last Pre-cache: <span id="lastPrecache"></span></p>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <button onclick="clearCache('all')" class="px-3 py-1 bg-red-100 text-red-800 text-sm rounded hover:bg-red-200">
                        Clear All Caches
                    </button>
                    <button onclick="clearCache('content')" class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded hover:bg-blue-200">
                        Clear Content Caches
                    </button>
                    <button onclick="preCacheFrequent()" class="px-3 py-1 bg-green-100 text-green-800 text-sm rounded hover:bg-green-200 col-span-2">
                        Pre-cache Frequent Comparisons
                    </button>
                </div>
            </div>
        </div>

        <!-- Cached Items -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Cached Comparisons</h2>
            <div class="overflow-x-auto max-h-64">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">Version Pair</th>
                            <th class="px-4 py-2 text-left">Size</th>
                            <th class="px-4 py-2 text-left">Last Accessed</th>
                        </tr>
                    </thead>
                    <tbody id="cachedItemsTable">
                        <!-- Will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- User Activity -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">User Comparison Activity</h2>
            <div class="h-64">
                <canvas id="userActivityChart"></canvas>
            </div>
        </div>

        <!-- Most Compared Pairs -->
        <div class="bg-white rounded-lg shadow p-6 col-span-2">
            <h2 class="text-xl font-semibold mb-4">Most Compared Version Pairs</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left">Version Pair</th>
                            <th class="px-4 py-2 text-left">Comparison Count</th>
                        </tr>
                    </thead>
                    <tbody id="versionPairsTable">
                        <!-- Will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Version Timeline -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Version Timeline</h2>
        <div id="versionTimeline" style="height: 400px;"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const contentId = {{ $contentId }};
    const analyticsData = @json($analytics);

    // Initialize Trends Chart
    const trendsCtx = document.getElementById('trendsChart').getContext('2d');
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: analyticsData.trends.labels,
            datasets: [{
                label: 'Comparisons',
                data: analyticsData.trends.data,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        }
    });

    // Initialize Cache Chart
    const cacheCtx = document.getElementById('cacheChart').getContext('2d');
    new Chart(cacheCtx, {
        type: 'doughnut',
        data: {
            labels: ['Cache Hits', 'Cache Misses'],
            datasets: [{
                data: [
                    analyticsData.cache_stats.cache_hits,
                    analyticsData.cache_stats.total - analyticsData.cache_stats.cache_hits
                ],
                backgroundColor: [
                    'rgb(54, 162, 235)',
                    'rgb(255, 99, 132)'
                ]
            }]
        }
    });

    // Set cache metrics
    const hitRate = (analyticsData.cache_stats.cache_hits / analyticsData.cache_stats.total * 100).toFixed(2);
    document.getElementById('cacheHitRate').textContent = `${hitRate}%`;
    document.getElementById('cacheSize').textContent = formatBytes(analyticsData.cache_stats.size_bytes);
    document.getElementById('cacheItemsCount').textContent = analyticsData.cache_stats.items_count;
    document.getElementById('lastPrecache').textContent = analyticsData.cache_stats.last_precache || 'Never';

    // Load cached items
    loadCachedItems(contentId);

    // Initialize User Activity Chart
    const userActivityCtx = document.getElementById('userActivityChart').getContext('2d');
    new Chart(userActivityCtx, {
        type: 'bar',
        data: {
            labels: Object.keys(analyticsData.user_activity),
            datasets: [{
                label: 'Comparisons',
                data: Object.values(analyticsData.user_activity),
                backgroundColor: 'rgba(153, 102, 255, 0.6)'
            }]
        }
    });

    // Initialize Version Timeline (using vis.js)
    const timelineContainer = document.getElementById('versionTimeline');
    const timelineItems = analyticsData.version_timeline.map(item => ({
        id: item.id,
        content: item.content,
        start: item.start,
        end: item.end
    }));

    const timeline = new vis.Timeline(timelineContainer, timelineItems, {
        showCurrentTime: true,
        zoomable: true
    });

    // Load additional data via AJAX
    loadVersionPairs(contentId);

    function clearCache(type) {
        fetch(`/content/${contentId}/analytics/clear-cache?type=${type}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Cache cleared successfully');
                location.reload();
            } else {
                alert('Error clearing cache');
            }
        });
    }
});

function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

function loadCachedItems(contentId) {
    fetch(`/content/${contentId}/analytics/cached-items`)
        .then(response => response.json())
        .then(data => {
            const tableBody = document.getElementById('cachedItemsTable');
            tableBody.innerHTML = data.map(item => `
                <tr>
                    <td class="px-4 py-2">${item.version_pair}</td>
                    <td class="px-4 py-2">${formatBytes(item.size)}</td>
                    <td class="px-4 py-2">${new Date(item.last_accessed).toLocaleString()}</td>
                </tr>
            `).join('');
        });
}

function preCacheFrequent() {
    const contentId = {{ $contentId }};
    fetch(`/content/${contentId}/analytics/pre-cache`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Pre-caching completed successfully');
            location.reload();
        } else {
            alert('Error during pre-caching');
        }
    });
}

function loadVersionPairs(contentId) {
    fetch(`/content/${contentId}/analytics/version-pairs`)
        .then(response => response.json())
        .then(data => {
            const tableBody = document.getElementById('versionPairsTable');
            tableBody.innerHTML = data.map(pair => `
                <tr>
                    <td class="px-4 py-2">${pair.version1} ↔ ${pair.version2}</td>
                    <td class="px-4 py-2">${pair.count}</td>
                </tr>
            `).join('');
        });
}
</script>
@endsection
