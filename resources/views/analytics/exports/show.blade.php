@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">{{ $export->name ?? 'Analytics Export' }}</h1>
            <div class="flex space-x-4">
                @if($export->status === 'completed' && $export->file_path)
                    <a href="{{ route('exports.download', $export) }}" 
                       class="btn btn-primary">
                        Download
                    </a>
                @endif
                <form action="{{ route('exports.destroy', $export) }}" 
                      method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-medium mb-4">Export Details</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="font-medium">
                            <x-export-status-badge :status="$export->status" />
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Created</p>
                        <p class="font-medium">
                            {{ $export->created_at->format('M j, Y g:i a') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Expires</p>
                        <p class="font-medium">
                            {{ $export->expires_at?->format('M j, Y g:i a') ?? 'Never' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">File Size</p>
                        <p class="font-medium">
                            {{ $export->file_size ? formatBytes($export->file_size) : 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>

            @if($export->status === 'failed' && $export->error_message)
                <div class="p-6 bg-red-50 border-b border-red-100">
                    <h3 class="text-lg font-medium text-red-800 mb-2">Error Details</h3>
                    <p class="text-red-700">{{ $export->error_message }}</p>
                </div>
            @endif

            <div class="p-6">
                <h3 class="text-lg font-medium mb-2">Export Parameters</h3>
                <pre class="bg-gray-50 p-4 rounded text-sm overflow-x-auto">{{ json_encode($export->parameters, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>

        <a href="{{ route('exports.index') }}" class="btn btn-secondary">
            Back to Exports
        </a>
    </div>
</div>
@endsection