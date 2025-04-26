@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Theme Version Comparison</h1>
            <a href="{{ route('themes.versions.index', $theme) }}" class="btn btn-secondary">
                Back to Versions
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="border-r border-gray-200 pr-4">
                    <h2 class="text-xl font-semibold mb-2">Version {{ $version1->version }}</h2>
                    <div class="text-sm text-gray-600 mb-2">
                        Created: {{ $version1->created_at->format('M d, Y H:i') }}
                    </div>
                    <div class="text-sm text-gray-600 mb-4">
                        Branch: {{ $version1->branch?->name ?? 'Main' }}
                    </div>
                    <div class="prose max-w-none">
                        {!! Markdown::parse($version1->changelog) !!}
                    </div>
                </div>

                <div class="pl-4">
                    <h2 class="text-xl font-semibold mb-2">Version {{ $version2->version }}</h2>
                    <div class="text-sm text-gray-600 mb-2">
                        Created: {{ $version2->created_at->format('M d, Y H:i') }}
                    </div>
                    <div class="text-sm text-gray-600 mb-4">
                        Branch: {{ $version2->branch?->name ?? 'Main' }}
                    </div>
                    <div class="prose max-w-none">
                        {!! Markdown::parse($version2->changelog) !!}
                    </div>
                </div>
            </div>

            @if($comparisonStats = app(App\Services\VersionComparisonService::class)->compareVersions($version1, $version2))
                <x-theme-version-comparison-stats :comparison="$comparisonStats" />

                <div class="mt-6 bg-white rounded-lg shadow p-4">
                    <h3 class="text-lg font-medium mb-4">Size Comparison Visualization</h3>
                    <div class="w-full h-96">
                        <canvas id="sizeComparisonChart"></canvas>
                    </div>
                </div>

                @push('scripts')
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const ctx = document.getElementById('sizeComparisonChart').getContext('2d');
                            const chart = new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: ['Version {{ $version1->version }}', 'Version {{ $version2->version }}'],
                                    datasets: [{
                                        label: 'Total Size (KB)',
                                        data: [
                                            {{ $version1->total_size_kb }}, 
                                            {{ $version2->total_size_kb }}
                                        ],
                                        backgroundColor: [
                                            'rgba(54, 162, 235, 0.5)',
                                            'rgba(255, 99, 132, 0.5)'
                                        ],
                                        borderColor: [
                                            'rgba(54, 162, 235, 1)',
                                            'rgba(255, 99, 132, 1)'
                                        ],
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            title: {
                                                display: true,
                                                text: 'Size (KB)'
                                            }
                                        }
                                    }
                                }
                            });
                        });
                    </script>
                @endpush
            @endif

            @livewire('theme-version-comparison', [
                'theme' => $theme,
                'version1' => $version1,
                'version2' => $version2
            ])

            @if(auth()->user()->can('restore', $version1))
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <form method="POST" action="{{ route('themes.versions.restore', [$theme, $version1]) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary" 
                            onclick="return confirm('Are you sure you want to restore this version?')">
                            Restore Version {{ $version1->version }}
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
