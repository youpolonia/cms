@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-4">Shared Version Comparison</h1>
        
        <div class="mb-6">
            <p class="text-gray-600 mb-2">This comparison was shared with you by {{ $sharedBy->name }}.</p>
            <p class="text-sm text-gray-500">Shared on {{ $sharedAt->format('M d, Y H:i') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="border rounded-lg p-4">
                <h2 class="text-lg font-semibold mb-2">Original Version</h2>
                <div class="prose max-w-none">
                    {!! $originalVersion->content !!}
                </div>
            </div>

            <div class="border rounded-lg p-4">
                <h2 class="text-lg font-semibold mb-2">Modified Version</h2>
                <div class="prose max-w-none">
                    {!! $modifiedVersion->content !!}
                </div>
            </div>
        </div>

        <div class="mt-8">
            <h2 class="text-xl font-semibold mb-4">Comparison Stats</h2>
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">Changes</p>
                    <p class="text-2xl font-bold">{{ $stats->change_count }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">Additions</p>
                    <p class="text-2xl font-bold">{{ $stats->additions }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-500">Deletions</p>
                    <p class="text-2xl font-bold">{{ $stats->deletions }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
