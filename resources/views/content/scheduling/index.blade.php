@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold">Scheduled Content</h1>
            <a href="{{ route('content.scheduling.calendar') }}" 
               class="px-4 py-2 bg-blue-500 text-white rounded-md">
                Calendar View
            </a>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <form method="GET" class="flex items-center">
                    <input type="text" name="search" placeholder="Search content..." 
                           value="{{ request('search') }}"
                           class="flex-1 rounded-md border-gray-300 shadow-sm">
                    <button type="submit" 
                            class="ml-2 px-4 py-2 bg-gray-100 rounded-md">
                        Search
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Version</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Publish At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unpublish At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($scheduledContent as $content)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900">{{ $content->title }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    v{{ $content->scheduledVersion->version_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $content->scheduledVersion->publish_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $content->scheduledVersion->unpublish_at?->format('Y-m-d H:i') ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('content.scheduling.edit', $content->scheduledVersion) }}" 
                                           class="text-blue-600 hover:text-blue-900">Edit</a>
                                        <form method="POST" 
                                              action="{{ route('content.scheduling.publishNow', $content->scheduledVersion) }}">
                                            @csrf
                                            <button type="submit" 
                                                    class="text-green-600 hover:text-green-900">
                                                Publish Now
                                            </button>
                                        </form>
                                        <form method="POST" 
                                              action="{{ route('content.scheduling.destroy', $content->scheduledVersion) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900">
                                                Cancel
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-4 sm:px-6">
                {{ $scheduledContent->links() }}
            </div>
        </div>
    </div>
@endsection