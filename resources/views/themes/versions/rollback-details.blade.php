@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Rollback Details for {{ $theme->name }}</h1>
            <a href="{{ route('themes.versions.rollback.history', [$theme, $rollback->version]) }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Back to History
            </a>
        </div>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Rollback Operation Summary
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    Detailed information about this rollback operation
                </p>
            </div>
            <div class="px-4 py-5 sm:p-0">
                <dl class="sm:divide-y sm:divide-gray-200">
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Rollback From
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            Version #{{ $rollback->version->id }} ({{ $rollback->version->created_at->format('M d, Y H:i') }})
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Rollback To
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            Version #{{ $rollback->rollbackToVersion->id }} ({{ $rollback->rollbackToVersion->created_at->format('M d, Y H:i') }})
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Status
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $rollback->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                   ($rollback->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($rollback->status) }}
                            </span>
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Initiated By
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $rollback->initiator->name }} ({{ $rollback->created_at->format('M d, Y H:i') }})
                        </dd>
                    </div>
                    @if($rollback->completed_at)
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Completed At
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $rollback->completed_at->format('M d, Y H:i') }}
                        </dd>
                    </div>
                    @endif
                    @if($rollback->error_message)
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Error Message
                        </dt>
                        <dd class="mt-1 text-sm text-red-900 sm:mt-0 sm:col-span-2">
                            {{ $rollback->error_message }}
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    File Changes
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    Summary of files modified during rollback
                </p>
            </div>
            <div class="px-4 py-5 sm:p-0">
                @if($rollback->file_changes)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    File Path
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Change Type
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Lines Changed
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach(json_decode($rollback->file_changes, true) as $change)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $change['path'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ ucfirst($change['type']) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $change['lines'] ?? 'N/A' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="px-4 py-4 text-center text-sm text-gray-500">
                    No file change details available
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg mt-8">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Rollback Analytics Dashboard
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Detailed analytics about theme rollback patterns and impacts
            </p>
        </div>
        
        <div class="px-4 py-5 sm:p-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button @click="activeTab = 'reasons'" 
                        :class="activeTab === 'reasons' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" 
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Rollback Reasons
                    </button>
                    <button @click="activeTab = 'impact'" 
                        :class="activeTab === 'impact' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" 
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Performance Impact
                    </button>
                    <button @click="activeTab = 'behavior'" 
                        :class="activeTab === 'behavior' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" 
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        User Behavior
                    </button>
                    <button @click="activeTab = 'notifications'" 
                        :class="activeTab === 'notifications' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" 
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Notification Preferences
                    </button>
                </nav>
            </div>

            <div x-data="{
                activeTab: 'reasons',
                reasonsChart: null,
                impactChart: null,
                behaviorChart: null,
                init() {
                    // Initialize charts when tab becomes visible
                    this.$watch('activeTab', (tab) => {
                        if (tab === 'reasons' && !this.reasonsChart) {
                            this.initReasonsChart();
                        }
                        if (tab === 'impact' && !this.impactChart) {
                            this.initImpactChart();
                        }
                        if (tab === 'behavior' && !this.behaviorChart) {
                            this.initBehaviorChart();
                        }
                    });
                    
                    // Load initial data
                    this.loadAnalyticsData();
                },
                async loadAnalyticsData() {
                    try {
                        const [reasons, impact, behavior, notifications] = await Promise.all([
                            axios.get(`/api/theme-rollbacks/${$rollback->id}/reasons`),
                            axios.get(`/api/theme-rollbacks/${$rollback->id}/impact`),
                            axios.get(`/api/theme-rollbacks/${$rollback->id}/user-behavior`),
                            axios.get(`/api/theme-rollbacks/${$rollback->id}/notification-preferences`)
                        ]);
                        
                        this.reasonsData = reasons.data.data;
                        this.impactData = impact.data.data;
                        this.behaviorData = behavior.data.data;
                        this.notificationData = notifications.data.data;
                        
                        // Initialize first chart
                        this.initReasonsChart();
                    } catch (error) {
                        console.error('Error loading analytics data:', error);
                    }
                },
                initReasonsChart() {
                    const ctx = this.$refs.reasonsChart.getContext('2d');
                    this.reasonsChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: this.reasonsData.map(item => item.reason),
                            datasets: [{
                                data: this.reasonsData.map(item => item.count),
                                backgroundColor: [
                                    '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'right',
                                },
                                title: {
                                    display: true,
                                    text: 'Rollback Reasons Distribution'
                                }
                            }
                        }
                    });
                },
                initImpactChart() {
                    const ctx = this.$refs.impactChart.getContext('2d');
                    this.impactChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: this.impactData.metrics,
                            datasets: [{
                                label: 'Impact Score',
                                data: this.impactData.scores,
                                backgroundColor: '#3B82F6',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            },
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Performance Impact Metrics'
                                }
                            }
                        }
                    });
                },
                initBehaviorChart() {
                    const ctx = this.$refs.behaviorChart.getContext('2d');
                    this.behaviorChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: this.behaviorData.dates,
                            datasets: [{
                                label: 'Rollback Frequency',
                                data: this.behaviorData.counts,
                                borderColor: '#3B82F6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'User Rollback Behavior Over Time'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
            }" class="mt-6">
                <div x-show="activeTab === 'reasons'" class="space-y-4">
                    <div class="h-96">
                        <canvas x-ref="reasonsChart"></canvas>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-900">Key Insights</h4>
                        <ul class="mt-2 text-sm text-gray-700 list-disc pl-5 space-y-1">
                            <li>Most common reason: <span x-text="reasonsData[0]?.reason"></span> (<span x-text="reasonsData[0]?.count"></span> occurrences)</li>
                            <li>Total rollbacks analyzed: <span x-text="reasonsData.reduce((sum, item) => sum + item.count, 0)"></span></li>
                        </ul>
                    </div>
                </div>

                <div x-show="activeTab === 'impact'" class="space-y-4">
                    <div class="h-96">
                        <canvas x-ref="impactChart"></canvas>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-900">Performance Impact Summary</h4>
                        <p class="mt-2 text-sm text-gray-700" x-text="impactData.summary"></p>
                    </div>
                </div>

                <div x-show="activeTab === 'behavior'" class="space-y-4">
                    <div class="h-96">
                        <canvas x-ref="behaviorChart"></canvas>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-900">Behavior Patterns</h4>
                        <p class="mt-2 text-sm text-gray-700" x-text="behaviorData.summary"></p>
                    </div>
                </div>

                <div x-show="activeTab === 'notifications'" class="space-y-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-900">Notification Preferences Summary</h4>
                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="bg-white p-4 rounded-lg shadow">
                                <h5 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Channels</h5>
                                <div class="mt-2 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-700">Email Notifications</span>
                                        <span x-text="notificationData.email_enabled ? 'Enabled' : 'Disabled'" 
                                              :class="notificationData.email_enabled ? 'text-green-600' : 'text-gray-500'"
                                              class="text-sm font-medium"></span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-700">In-App Notifications</span>
                                        <span x-text="notificationData.in_app_enabled ? 'Enabled' : 'Disabled'" 
                                              :class="notificationData.in_app_enabled ? 'text-green-600' : 'text-gray-500'"
                                              class="text-sm font-medium"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white p-4 rounded-lg shadow">
                                <h5 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Event Types</h5>
                                <div class="mt-2 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-700">Rollback Requests</span>
                                        <span x-text="notificationData.request_enabled ? 'Enabled' : 'Disabled'" 
                                              :class="notificationData.request_enabled ? 'text-green-600' : 'text-gray-500'"
                                              class="text-sm font-medium"></span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-700">Rollback Completions</span>
                                        <span x-text="notificationData.completion_enabled ? 'Enabled' : 'Disabled'" 
                                              :class="notificationData.completion_enabled ? 'text-green-600' : 'text-gray-500'"
                                              class="text-sm font-medium"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">Notification Settings</h4>
                        <div class="mt-4">
                            @include('components.rollback-notification-settings', ['user' => auth()->user()])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@endsection
