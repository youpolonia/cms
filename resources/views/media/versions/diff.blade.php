@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Version Differences for {{ $media->filename }}</h1>
        <a href="{{ route('media.versions.index', $media) }}" 
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
            Back to Versions
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Comparing Version {{ $version1->version_number }} with Version {{ $version2->version_number }}</h2>
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

            <div class="border rounded-lg overflow-hidden">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Line</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Version {{ $version1->version_number }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Version {{ $version2->version_number }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($diffs as $diff)
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-500">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2 text-sm @if($diff['type'] === 'removed') bg-red-50 @elseif($diff['type'] === 'changed') bg-yellow-50 @endif">
                                @if($diff['type'] !== 'added') {{ $diff['old'] }} @endif
                            </td>
                            <td class="px-4 py-2 text-sm @if($diff['type'] === 'added') bg-green-50 @elseif($diff['type'] === 'changed') bg-yellow-50 @endif">
                                @if($diff['type'] !== 'removed') {{ $diff['new'] }} @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="flex justify-center space-x-4">
        @if($version1->version_number > 1)
        <a href="{{ route('media.versions.diff', [$media, $version1->version_number - 1, $version2->version_number]) }}" 
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
            Previous Version
        </a>
        @endif

        @if($version2->version_number < $media->version_count)
        <a href="{{ route('media.versions.diff', [$media, $version1->version_number, $version2->version_number + 1]) }}" 
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
            Next Version
        </a>
        @endif
    </div>
</div>
@endsection
