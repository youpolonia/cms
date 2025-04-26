@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Autopilot Dashboard</h1>

    <div class="grid grid-cols-1 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            @livewire('autopilot-queue-status')
        </div>

        @livewire('autopilot-task-manager')

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium mb-4">
                Recent Tasks
                @if($filterStatus !== 'all')
                    <span class="text-sm font-normal text-gray-500">(Filtered: {{ ucfirst($filterStatus) }})</span>
                @endif
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentTasks as $task)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $task->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $task->type }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $task->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                       ($task->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ $task->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $task->created_at->diffForHumans() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection