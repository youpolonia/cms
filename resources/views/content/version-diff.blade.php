@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Version Comparison for: {{ $content->title }}</h1>
        <a href="{{ route('content.versions.compare', $content) }}" class="text-blue-600 hover:text-blue-800">
            ← Back to version list
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold">Comparing Versions</h2>
        </div>
        <div class="p-4 grid grid-cols-2 gap-4">
            <div>
                <div class="font-medium">Version #{{ $version1->version_number }}</div>
                <div class="text-sm text-gray-500">
                    {{ $version1->created_at->format('M j, Y g:i A') }} by {{ $version1->user->name }}
                </div>
                @if($version1->notes)
                    <div class="text-sm mt-1 text-gray-600">
                        {{ $version1->notes }}
                    </div>
                @endif
            </div>
            <div>
                <div class="font-medium">Version #{{ $version2->version_number }}</div>
                <div class="text-sm text-gray-500">
                    {{ $version2->created_at->format('M j, Y g:i A') }} by {{ $version2->user->name }}
                </div>
                @if($version2->notes)
                    <div class="text-sm mt-1 text-gray-600">
                        {{ $version2->notes }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="grid grid-cols-2 divide-x">
            <div class="p-4">
                <h3 class="font-medium mb-2">Version #{{ $version1->version_number }}</h3>
                <div class="font-mono text-sm">
                    @foreach($diff['old'] as $line)
                        <div class="py-1 px-2 @if($line['type'] === 'removed') bg-red-100 @elseif($line['type'] === 'unchanged') bg-gray-50 @endif">
                            {{ $line['text'] }}
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="p-4">
                <h3 class="font-medium mb-2">Version #{{ $version2->version_number }}</h3>
                <div class="font-mono text-sm">
                    @foreach($diff['new'] as $line)
                        <div class="py-1 px-2 @if($line['type'] === 'added') bg-green-100 @elseif($line['type'] === 'unchanged') bg-gray-50 @endif">
                            {{ $line['text'] }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection