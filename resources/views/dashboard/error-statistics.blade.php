@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Error Classification Statistics</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        @foreach($stats as $period => $data)
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Last {{ $period }}</h2>
            
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-500">Total Errors</p>
                    <p class="text-2xl font-bold">{{ $data['total'] }}</p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Auto-Classified</p>
                    <p class="text-2xl font-bold">{{ $data['auto_classified'] }} ({{ round($data['auto_classified']/$data['total']*100, 1) }}%)</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-lg font-semibold mb-4">Errors by Category</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($categories as $category)
            <div class="flex items-center">
                <div class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $category->color }}"></div>
                <span class="flex-1">{{ $category->name }}</span>
                <span class="font-medium">{{ $stats['30d']['by_category'][$category->name] ?? 0 }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection