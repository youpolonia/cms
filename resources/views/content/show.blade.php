@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-3xl font-bold">{{ $content->title }}</h1>
                <p class="text-gray-600 mt-2">
                    Type: {{ $content->type->name }} | 
                    Author: {{ $content->author->name }} | 
                    Status: <span class="font-semibold {{ $content->status === 'published' ? 'text-green-600' : 'text-yellow-600' }}">
                        {{ ucfirst($content->status) }}
                    </span>
                </p>
            </div>
            <div class="flex space-x-2">
                @can('update', $content)
                    <a href="{{ route('content.edit', $content) }}"
                       class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        Edit Content
                    </a>
                @endcan
                
                @if($content->is_scheduled)
                    <form action="{{ route('contents.schedule.destroy', $content) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                            Cancel Schedule
                        </button>
                    </form>
                @else
                    <a href="{{ route('contents.schedule.create', $content) }}"
                       class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        Schedule
                    </a>
                @endif
            </div>
        </div>

        @if($content->is_scheduled)
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            Scheduled to publish on {{ $content->scheduled_publish_at->format('M j, Y g:i a') }}
                            @if($content->scheduled_unpublish_at)
                                and unpublish on {{ $content->scheduled_unpublish_at->format('M j, Y g:i a') }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endif
        
        <div class="prose max-w-none">
            {!! $content->body !!}
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-4">Version History</h2>
        
        <div class="space-y-4">
            @foreach($content->versions as $version)
                <div class="border rounded-lg p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="font-medium">{{ $version->version_display }}</h3>
                            <p class="text-sm text-gray-600">
                                {{ $version->created_at->format('M j, Y g:i a') }} by {{ $version->user->name }}
                            </p>
                            @if($version->change_description)
                                <p class="mt-1 text-gray-700">{{ $version->change_description }}</p>
                            @endif
                        </div>
                        @can('update', $content)
                            <form action="{{ route('content.restore-version', $version) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Restore this version
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
