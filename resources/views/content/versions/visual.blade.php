@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Visual Comparison</h1>
        <a href="{{ route('content.versions.show', [$content, $version1]) }}" 
           class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
            Back to Version
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h2 class="text-lg font-semibold mb-2">Version #{{ $version1->version_number }}</h2>
                <div class="prose max-w-none bg-gray-50 p-4 rounded-lg border border-gray-200">
                    {!! $version1->content !!}
                </div>
            </div>
            <div>
                <h2 class="text-lg font-semibold mb-2">Version #{{ $version2->version_number }}</h2>
                <div class="prose max-w-none bg-gray-50 p-4 rounded-lg border border-gray-200">
                    {!! $version2->content !!}
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-4">Summary of Changes</h2>
        <div class="prose max-w-none">
            <ul class="list-disc pl-5">
                @foreach($version1->diffWith($version2) as $change)
                    <li class="{{ $change['type'] === 'added' ? 'text-green-600' : ($change['type'] === 'removed' ? 'text-red-600' : 'text-blue-600') }}">
                        {{ $change['description'] }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
