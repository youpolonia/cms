@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Version {{ $version->version_number }} of {{ $media->filename }}</h1>
        <div class="flex space-x-2">
            <a href="{{ route('media.versions.index', $media) }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                Back to Versions
            </a>
            @if($version->version_number !== $media->current_version)
                <form action="{{ route('media.versions.restore', $media) }}" method="POST">
                    @csrf
                    <input type="hidden" name="version_number" value="{{ $version->version_number }}">
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                        Restore This Version
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Version Details</h2>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Created</p>
                    <p class="text-sm font-medium">{{ $version->created_at->format('M d, Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">By</p>
                    <p class="text-sm font-medium">{{ $version->user->name }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Metadata</h2>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($version->metadata as $key => $value)
                <div>
                    <p class="text-sm text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}</p>
                    <p class="text-sm font-medium">{{ is_array($value) ? json_encode($value) : $value }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    @if(!empty($version->changes))
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Changes from Previous Version</h2>
        </div>
        <div class="px-6 py-4">
            <ul class="list-disc pl-5 space-y-2">
                @foreach($version->changes as $field => $change)
                <li class="text-sm">
                    <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $field)) }}:</span>
                    <span class="text-gray-700">{{ is_array($change) ? json_encode($change) : $change }}</span>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Version Comment</h2>
        </div>
        <div class="px-6 py-4">
            <p class="text-sm text-gray-700">
                {{ $version->comment ?? 'No comment provided for this version' }}
            </p>
        </div>
    </div>

    @if($version->tags)
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Tags</h2>
        </div>
        <div class="px-6 py-4">
            <div class="flex flex-wrap gap-2">
                @foreach($version->tags as $tag)
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                        {{ $tag }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>
    @endif

                <div class="mt-6">
                    <h3 class="text-lg font-medium text-gray-900">Version Details</h3>
                    <div class="mt-4 space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Version Number</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $version->version_number }}</p>
                        </div>
                        @if($version->comment)
                        <div>
                            <p class="text-sm font-medium text-gray-500">Comment</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $version->comment }}</p>
                        </div>
                        @endif
@endsection
