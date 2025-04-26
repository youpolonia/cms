@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Restore Version</h1>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-lg font-semibold">Version Details</h2>
                <p class="text-sm text-gray-600">
                    Created: {{ $targetVersion->created_at->format('M d, Y H:i') }}
                    by {{ $targetVersion->creator->name ?? 'System' }}
                </p>
            </div>
            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                Version #{{ $targetVersion->version_number }}
            </span>
        </div>

        <div class="mb-6">
            <h3 class="font-medium mb-2">Changes from current version:</h3>
            <div class="border rounded p-4 bg-gray-50">
                @if(isset($diff['html_diff']))
                    {!! $diff['html_diff'] !!}
                @else
                    <pre class="whitespace-pre-wrap">{{ $diff['diff'] }}</pre>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('versions.restore', $targetVersion) }}">
            @csrf
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="confirm" class="rounded border-gray-300 mr-2">
                    <span>I confirm I want to restore this version</span>
                </label>
            </div>
            <div class="flex space-x-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Restore Version
                </button>
                <a href="{{ route('contents.show', $targetVersion->content) }}" 
                   class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection