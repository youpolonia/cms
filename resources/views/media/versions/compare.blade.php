@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Comparing Versions of {{ $media->filename }}</h1>
        <a href="{{ route('media.versions.index', $media) }}" 
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
            Back to Versions
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Comparison</h2>
        </div>
        <div class="px-6 py-4">
            <div class="flex justify-center mb-4">
                <div class="text-center px-4 py-2 bg-blue-100 rounded-lg">
                    <p class="text-sm font-medium">Version {{ $version1->version_number }}</p>
                    <p class="text-xs text-gray-500">{{ $version1->created_at->format('M d, Y H:i') }}</p>
                </div>
                <div class="flex items-center px-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="text-center px-4 py-2 bg-blue-100 rounded-lg">
                    <p class="text-sm font-medium">Version {{ $version2->version_number }}</p>
                    <p class="text-xs text-gray-500">{{ $version2->created_at->format('M d, Y H:i') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="border rounded-lg p-4">
                    <h3 class="text-lg font-medium mb-4">Version {{ $version1->version_number }} Details</h3>
                    <div class="space-y-4">
                        @foreach($version1->metadata as $key => $value)
                        <div>
                            <p class="text-sm text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}</p>
                            <p class="text-sm font-medium @if($version2->metadata[$key] != $value) bg-yellow-100 @endif">
                                {{ is_array($value) ? json_encode($value) : $value }}
                            </p>
                        </div>
                        @endforeach
                        <div>
                            <p class="text-sm text-gray-500">Comment</p>
                            <p class="text-sm font-medium @if($version1->comment != $version2->comment) bg-yellow-100 @endif">
                                {{ $version1->comment ?? 'No comment' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="border rounded-lg p-4">
                    <h3 class="text-lg font-medium mb-4">Version {{ $version2->version_number }} Details</h3>
                    <div class="space-y-4">
                        @foreach($version2->metadata as $key => $value)
                        <div>
                            <p class="text-sm text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}</p>
                            <p class="text-sm font-medium @if($version1->metadata[$key] != $value) bg-yellow-100 @endif">
                                {{ is_array($value) ? json_encode($value) : $value }}
                            </p>
                        </div>
                        @endforeach
                        <div>
                            <p class="text-sm text-gray-500">Comment</p>
                            <p class="text-sm font-medium @if($version1->comment != $version2->comment) bg-yellow-100 @endif">
                                {{ $version2->comment ?? 'No comment' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-center space-x-4">
        @if($version1->version_number > 1)
        <a href="{{ route('media.versions.compare', [$media, $version1->version_number - 1, $version2->version_number]) }}" 
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
            Previous Version
        </a>
        @endif

        @if($version2->version_number < $media->version_count)
        <a href="{{ route('media.versions.compare', [$media, $version1->version_number, $version2->version_number + 1]) }}" 
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
            Next Version
        </a>
        @endif
    </div>
</div>
@endsection
