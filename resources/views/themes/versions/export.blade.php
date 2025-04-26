@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">
            Export: {{ $theme->name }} v{{ $version->getSemanticVersion() }}
        </h1>
        <a href="{{ route('themes.versions.show', [$theme, $version]) }}" 
           class="btn btn-secondary">
            Back to Version
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden mb-8">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Export Options</h2>
            
            <form action="{{ route('themes.versions.export', [$theme, $version]) }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Format</label>
                        <select name="format" class="form-select">
                            <option value="zip">ZIP Archive</option>
                            <option value="json">JSON Metadata</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Include</label>
                        <div class="space-y-2">
                            <div class="flex items-center">
                                <input type="checkbox" id="includeAssets" name="include_assets" checked 
                                    class="form-checkbox h-4 w-4 text-indigo-600">
                                <label for="includeAssets" class="ml-2 text-sm text-gray-700">
                                    Theme Assets
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="includeMetadata" name="include_metadata" checked 
                                    class="form-checkbox h-4 w-4 text-indigo-600">
                                <label for="includeMetadata" class="ml-2 text-sm text-gray-700">
                                    Version Metadata
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    Export Version
                </button>
            </form>
        </div>
    </div>
    
    @if($version->export_count > 0)
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Export History</h2>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium">Export Count</p>
                        <p class="text-sm text-gray-500">{{ $version->export_count }}</p>
                    </div>
                    <div>
                        <p class="font-medium">Last Exported</p>
                        <p class="text-sm text-gray-500">
                            {{ $version->last_exported_at ? $version->last_exported_at->diffForHumans() : 'Never' }}
                        </p>
                    </div>
                </div>
                
                @if($version->export_size)
                <div>
                    <p class="font-medium">Last Export Size</p>
                    <p class="text-sm text-gray-500">
                        {{ number_format($version->export_size / 1024, 2) }} KB
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
