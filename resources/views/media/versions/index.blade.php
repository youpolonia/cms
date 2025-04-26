@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Versions for {{ $media->filename }}</h1>
        <div class="flex space-x-2">
            <form method="GET" action="{{ route('media.versions.index', $media) }}" class="flex items-center">
                <input type="text" name="tag" placeholder="Filter by tag" 
                       value="{{ request('tag') }}"
                       class="px-3 py-2 border rounded-md text-sm">
                <select name="branch" class="ml-2 px-3 py-2 border rounded-md text-sm">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->name }}" {{ request('branch') == $branch->name ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="ml-2 bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-md text-sm">
                    Filter
                </button>
                @if(request('tag') || request('branch'))
                    <a href="{{ route('media.versions.index', $media) }}" 
                       class="ml-2 bg-gray-500 hover:bg-gray-600 text-white px-3 py-2 rounded-md text-sm">
                        Clear
                    </a>
                @endif
            </form>
            <a href="{{ route('media.versions.create', $media) }}" 
               class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                New Version
            </a>
            <a href="{{ route('media.show', $media) }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                Back to Media
            </a>
        </div>
    </div>

    <div class="mb-6 bg-white rounded-lg shadow p-4">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold">Branches</h2>
            <a href="{{ route('media.versions.create-branch', $media) }}" 
               class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                Create Branch
            </a>
        </div>
        <div class="mt-2 grid grid-cols-1 md:grid-cols-3 gap-2">
            @foreach($branches as $branch)
                <div class="border rounded p-3 {{ $branch->is_default ? 'border-blue-300 bg-blue-50' : '' }}">
                    <div class="flex justify-between">
                        <div>
                            <h3 class="font-medium">{{ $branch->name }}</h3>
                            <p class="text-sm text-gray-600">{{ $branch->description }}</p>
                        </div>
                        <div class="text-sm">
                            @if($branch->is_default)
                                <span class="text-blue-600">Default</span>
                            @endif
                        </div>
                    </div>
                    <div class="mt-2 text-xs text-gray-500">
                        Base: v{{ $branch->baseVersion->version_number ?? 'N/A' }}
                    </div>
                    <div class="mt-2 text-xs text-gray-500">
                        Latest: v{{ $branch->latestVersion->version_number ?? 'N/A' }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Version</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Changes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($versions as $version)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="text-sm font-medium text-gray-900">
                                v{{ $version->version_number }}
                                @if($version->branch_name)
                                    <span class="ml-1 text-xs text-gray-500">({{ $version->branch_name }})</span>
                                @endif
                                @if($version->version_number === $media->current_version)
                                    <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Current
                                    </span>
                                @endif
                            </div>
                        </div>
                    </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $version->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $version->user->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $version->comment ? Str::limit($version->comment, 50) : 'No comment' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($version->tags)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($version->tags as $tag)
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800">
                                            {{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $version->changes }}
                        </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $version->comment ?? 'No comment' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('media.versions.show', [$media, $version]) }}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                        @if($version->version_number !== $media->current_version)
                            <form action="{{ route('media.versions.restore', $media) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="version_number" value="{{ $version->version_number }}">
                                <button type="submit" class="text-green-600 hover:text-green-900">Restore</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $versions->links() }}
    </div>
</div>
@endsection
