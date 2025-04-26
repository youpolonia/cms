@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Audit Log Details</h1>
        <a href="{{ route('audit-logs.index') }}" class="text-indigo-600 hover:text-indigo-900">Back to Logs</a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                    {{ $auditLog->action === 'created' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $auditLog->action === 'updated' ? 'bg-blue-100 text-blue-800' : '' }}
                    {{ $auditLog->action === 'deleted' ? 'bg-red-100 text-red-800' : '' }}">
                    {{ ucfirst($auditLog->action) }}
                </span>
                <span class="ml-2 text-gray-500">{{ $auditLog->created_at->format('Y-m-d H:i:s') }}</span>
            </div>
        </div>

        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Description</h3>
                    <p class="mt-1 text-sm text-gray-900">{{ $auditLog->description }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">User</h3>
                    <p class="mt-1 text-sm text-gray-900">{{ $auditLog->user->name ?? 'System' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">Affected Item</h3>
                    <p class="mt-1 text-sm text-gray-900">
                        {{ class_basename($auditLog->auditable_type) }} #{{ $auditLog->auditable_id }}
                    </p>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-500">IP Address</h3>
                    <p class="mt-1 text-sm text-gray-900">{{ $auditLog->ip_address ?? 'N/A' }}</p>
                </div>
            </div>

            @if($auditLog->metadata)
            <div class="mt-6">
                <h3 class="text-sm font-medium text-gray-500">Metadata</h3>
                <div class="mt-1 bg-gray-50 p-4 rounded">
                    <pre class="text-xs text-gray-800">{{ json_encode($auditLog->metadata, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection