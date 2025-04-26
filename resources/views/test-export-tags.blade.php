@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Export Tagging System Test</h1>
    
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Export Details</h2>
        <p><strong>Name:</strong> {{ $export->name }}</p>
        <p><strong>Status:</strong> {{ $export->status }}</p>
        
        <h3 class="text-lg font-medium mt-4 mb-2">Assigned Tags</h3>
        <div class="flex flex-wrap gap-2">
            @forelse($export->tags as $tag)
                <span class="px-2 py-1 rounded text-xs" 
                    style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                    {{ $tag->name }}
                </span>
            @empty
                <p class="text-gray-500">No tags assigned</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">All Available Tags</h2>
        <div class="flex flex-wrap gap-2">
            @foreach($allTags as $tag)
                <span class="px-2 py-1 rounded text-xs" 
                    style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                    {{ $tag->name }} ({{ $tag->exports_count }})
                </span>
            @endforeach
        </div>
    </div>

    <div class="mt-8">
        <livewire:export-tag-manager :export="$export" />
    </div>
</div>
@endsection