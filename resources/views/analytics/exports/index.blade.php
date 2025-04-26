@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold">Analytics Exports</h1>
        <a href="{{ route('exports.create') }}" class="btn btn-primary">
            New Export
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="divide-y divide-gray-200">
            @forelse($exports as $export)
                <div class="p-6 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium">
                                {{ $export->name ?? 'Analytics Export' }}
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Created: {{ $export->created_at->format('M j, Y g:i a') }}
                                @if($export->expires_at)
                                    • Expires: {{ $export->expires_at->format('M j, Y g:i a') }}
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <x-export-status-badge :status="$export->status" />

                            @if($export->status === 'completed' && $export->file_path)
                                <a href="{{ route('exports.download', $export) }}" 
                                   class="text-blue-600 hover:text-blue-800">
                                    Download
                                </a>
                            @endif

                            <form action="{{ route('exports.destroy', $export) }}" 
                                  method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-gray-500">
                    No exports found. Create your first analytics export.
                </div>
            @endforelse
        </div>

        @if($exports->hasPages())
            <div class="px-6 py-4 bg-gray-50">
                {{ $exports->links() }}
            </div>
        @endif
    </div>
</div>
@endsection