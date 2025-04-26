@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">
        Comparing Versions {{ $versionA->version_number }} and {{ $versionB->version_number }} of: {{ $content->title }}
    </h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">
                Version {{ $versionA->version_number }} ({{ $versionA->created_at->format('M j, Y') }})
            </h2>
            <div class="prose max-w-none">
                {!! $diff['content']['old'] !!}
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">
                Version {{ $versionB->version_number }} ({{ $versionB->created_at->format('M j, Y') }})
            </h2>
            <div class="prose max-w-none">
                {!! $diff['content']['new'] !!}
            </div>
        </div>
    </div>

    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">Changes Summary</h2>
        <div class="space-y-4">
            @foreach($diff['metadata'] as $field => $change)
            <div>
                <h3 class="font-medium">{{ ucfirst(str_replace('_', ' ', $field)) }}</h3>
                <div class="grid grid-cols-2 gap-4 mt-2">
                    <div class="bg-red-50 p-3 rounded">{{ $change['old'] ?? 'None' }}</div>
                    <div class="bg-green-50 p-3 rounded">{{ $change['new'] ?? 'None' }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
