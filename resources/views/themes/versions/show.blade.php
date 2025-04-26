@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Version {{ $version->getSemanticVersion() }}</h1>
            <p class="text-sm text-gray-600 mt-1">
                Branch: {{ $version->branch_name ?? 'Main' }}
                @if($version->is_active)
                    <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                        Active
                    </span>
                @endif
            </p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('themes.versions.index', $theme) }}" 
               class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                Back to Versions
            </a>
            @can('view', $theme)
            <a href="{{ route('themes.versions.preview', [$theme, $version]) }}"
               class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded">
                Preview Version
            </a>
            @endcan
            @can('export', $version)
            <div class="flex space-x-2">
                <button onclick="exportVersion('zip')"
                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Export ZIP
                </button>
                <button onclick="exportVersion('json')"
                    class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                    Export JSON
                </button>
                <button onclick="queueExport()"
                    class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600">
                    Queue Export
                </button>
            </div>

            <script>
                function exportVersion(format) {
                    fetch(`/api/themes/versions/${@json($version->id)}/export?format=${format}`)
                        .then(response => response.blob())
                        .then(blob => {
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = `theme-${@json($version->theme->slug)}-v${@json($version->version)}.${format}`;
                            document.body.appendChild(a);
                            a.click();
                            window.URL.revokeObjectURL(url);
                        });
                }

                function queueExport() {
                    fetch(`/api/themes/versions/${@json($version->id)}/export?queue=true`)
                        .then(response => response.json())
                        .then(data => {
                            alert('Export queued! You will be notified when ready.');
                        });
                }
            </script>
                <button type="submit" 
                        class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded">
                    Export Version
                </button>
            </form>
            @endcan
            @can('update', $theme)
            <form method="POST" action="{{ route('themes.versions.rollback', [$theme, $version]) }}">
                @csrf
                <button type="submit" 
                        onclick="return confirm('Are you sure you want to rollback to this version? A new version will be created.')"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">
                    Rollback To This Version
                </button>
            </form>
            <form method="POST" action="{{ route('themes.versions.restore', [$theme, $version]) }}">
                @csrf
                <button type="submit" 
                        onclick="return confirm('Are you sure you want to restore this version? A new version will be created with the restored content and this version\\'s restore count will be incremented.')"
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                    Restore This Version
                </button>
            </form>
            @endcan
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Status</h3>
                <p class="mt-1 text-sm text-gray-900">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $version->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $version->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500">Created By</h3>
                <p class="mt-1 text-sm text-gray-900">{{ $version->creator->name ?? 'System' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500">Created At</h3>
                <p class="mt-1 text-sm text-gray-900">{{ $version->created_at->format('M d, Y H:i') }}</p>
            </div>
            @if($version->export_count > 0)
            <div>
                <h3 class="text-sm font-medium text-gray-500">Export Count</h3>
                <p class="mt-1 text-sm text-gray-900">{{ $version->export_count }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500">Last Exported</h3>
                <p class="mt-1 text-sm text-gray-900">{{ $version->last_exported_at?->format('M d, Y H:i') ?? 'Never' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500">Export Size</h3>
                <p class="mt-1 text-sm text-gray-900">{{ $version->export_size ? formatBytes($version->export_size) : 'N/A' }}</p>
            </div>
            @endif
            @if($version->restore_count > 0)
            <div>
                <h3 class="text-sm font-medium text-gray-500">Restore Count</h3>
                <p class="mt-1 text-sm text-gray-900">{{ $version->restore_count }}</p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500">Last Restored</h3>
                <p class="mt-1 text-sm text-gray-900">{{ $version->last_restored_at?->format('M d, Y H:i') ?? 'Never' }}</p>
            </div>
            @endif
        </div>

        <div class="mb-6">
            <h3 class="text-sm font-medium text-gray-500">Description</h3>
            <p class="mt-1 text-sm text-gray-900">{{ $version->description }}</p>
        </div>

        <div class="mb-6">
            <h3 class="text-sm font-medium text-gray-500">Changelog</h3>
            <div class="prose max-w-none bg-gray-50 p-4 rounded-lg">
                {!! nl2br(e($version->changelog)) !!}
            </div>
        </div>

        @if($version->tags)
        <div class="mb-6">
            <h3 class="text-sm font-medium text-gray-500">Tags</h3>
            <div class="flex flex-wrap gap-2 mt-1">
                @foreach($version->tags as $tag)
                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                    {{ $tag }}
                </span>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <x-approval-timeline :version="$version" />
    </div>

    @if(!$version->is_active)
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-4">Compare With Active Version</h2>
        <a href="{{ route('themes.versions.compare', [$theme, $theme->versions()->active()->first(), $version]) }}" 
           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700">
            Compare Changes
        </a>
    </div>
    @endif
</div>
@endsection
