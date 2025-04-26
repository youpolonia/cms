@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Version {{ $version->version_number }} of: {{ $version->content->title }}</h1>
        <div class="text-sm text-gray-500">
            Created: {{ $version->created_at->format('M d, Y H:i') }} by {{ $version->user->name }}
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        @if($version->notes)
            <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-500">
                <h3 class="font-semibold mb-2">Version Notes</h3>
                <p>{{ $version->notes }}</p>
            </div>
        @endif

        <div class="prose max-w-none">
            {!! $version->content_data !!}
        </div>

        <div class="mt-8 flex space-x-4">
            @can('restore_content_versions')
                <form action="{{ route('content.versions.restore', [$version->content_id, $version->id]) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                        Restore This Version
                    </button>
                </form>
            @endcan
            <a href="{{ route('content.versions.compare', $version->content_id) }}" 
               class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                Back to Version List
            </a>
        </div>
    </div>
</div>
@endsection