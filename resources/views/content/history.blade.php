@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Version History for: {{ $content->title }}</h1>
        <div class="flex space-x-4">
            <form method="GET" action="{{ route('content.history', $content) }}" class="flex">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search notes..." 
                       class="rounded-l-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-r-md">
                    Search
                </button>
            </form>
            <a href="{{ route('content.history.export', array_merge(['content' => $content->id], request()->query())) }}" 
               class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export
            </a>
        </div>
    </div>

    <!-- Rest of existing history view remains unchanged -->
</div>
@endsection