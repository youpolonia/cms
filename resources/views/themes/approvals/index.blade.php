@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Theme Version Approval Queue</h1>
        
        <div class="flex space-x-4">
            <div class="relative">
                <select id="workflow-filter" class="appearance-none bg-white border border-gray-300 rounded-md pl-3 pr-8 py-2 text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Workflows</option>
                    @foreach($workflows as $workflow)
                        <option value="{{ $workflow->id }}" {{ request('workflow') == $workflow->id ? 'selected' : '' }}>
                            {{ $workflow->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="relative">
                <select id="priority-filter" class="appearance-none bg-white border border-gray-300 rounded-md pl-3 pr-8 py-2 text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Priorities</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                </select>
            </div>
            
            <div class="relative">
                <input type="text" id="theme-search" placeholder="Search themes..." 
                       class="bg-white border border-gray-300 rounded-md pl-3 pr-8 py-2 text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                       value="{{ request('search') }}">
            </div>
        </div>
    </div>

    @if($pendingApprovals->isEmpty())
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600">No pending approvals at this time.</p>
        </div>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'theme', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center">
                                Theme
                                @if(request('sort') === 'theme')
                                    <x-icons.sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} class="ml-1 h-3 w-3" />
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'version', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center">
                                Version
                                @if(request('sort') === 'version')
                                    <x-icons.sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} class="ml-1 h-3 w-3" />
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'submitted', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center">
                                Submitted
                                @if(request('sort') === 'submitted')
                                    <x-icons.sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} class="ml-1 h-3 w-3" />
                                @endif
                            </a>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Workflow</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($pendingApprovals as $version)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="text-sm font-medium text-gray-900">{{ $version->theme->name }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $version->getSemanticVersion() }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $version->submitted_for_approval_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $version->workflow->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-full mr-2">
                                    <div class="h-2 bg-gray-200 rounded-full">
                                        <div class="h-2 bg-blue-500 rounded-full" 
                                             style="width: {{ ($version->completedStepsCount() / $version->workflow->steps->count()) * 100 }}%"></div>
                                    </div>
                                </div>
                                <span class="text-xs text-gray-500">
                                    {{ $version->completedStepsCount() }}/{{ $version->workflow->steps->count() }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $hoursSinceSubmission = $version->submitted_for_approval_at->diffInHours();
                                $priority = $hoursSinceSubmission > 72 ? 'high' : ($hoursSinceSubmission > 24 ? 'medium' : 'low');
                            @endphp
                            <span class="px-2 py-1 text-xs rounded-full 
                                {{ $priority === 'high' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $priority === 'low' ? 'bg-green-100 text-green-800' : '' }}">
                                {{ ucfirst($priority) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('theme-approvals.show', $version) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Review</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const workflowFilter = document.getElementById('workflow-filter');
    const priorityFilter = document.getElementById('priority-filter');
    const themeSearch = document.getElementById('theme-search');
    const searchParams = new URLSearchParams(window.location.search);

    function updateFilters() {
        if (workflowFilter.value) {
            searchParams.set('workflow', workflowFilter.value);
        } else {
            searchParams.delete('workflow');
        }

        if (priorityFilter.value) {
            searchParams.set('priority', priorityFilter.value);
        } else {
            searchParams.delete('priority');
        }

        if (themeSearch.value) {
            searchParams.set('search', themeSearch.value);
        } else {
            searchParams.delete('search');
        }

        window.location.search = searchParams.toString();
    }

    workflowFilter.addEventListener('change', updateFilters);
    priorityFilter.addEventListener('change', updateFilters);
    
    // Add debounce to search input
    let debounceTimer;
    themeSearch.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(updateFilters, 500);
    });
});
</script>
@endpush
