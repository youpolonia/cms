@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">
            Preview: {{ $theme->name }} v{{ $version->getSemanticVersion() }}
        </h1>
        <a href="{{ route('themes.versions.show', [$theme, $version]) }}" 
           class="btn btn-secondary">
            Back to Version
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden mb-8">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Version Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Created</p>
                    <p>{{ $version->created_at->format('M j, Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Branch</p>
                    <p>{{ $version->branch_name ?? 'main' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Changelog</p>
                    <p>{{ $version->changelog }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Tags</p>
                    <p>
                        @forelse($version->tags ?? [] as $tag)
                            <span class="badge badge-primary mr-1">{{ $tag }}</span>
                        @empty
                            No tags
                        @endforelse
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Theme Configuration</h2>
                <pre class="bg-gray-100 p-4 rounded text-sm overflow-x-auto">{{ json_encode($config, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Files</h2>
                <div class="space-y-2">
                    @foreach($files as $file)
                        <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                            <span class="font-mono text-sm">{{ $file['path'] }}</span>
                            <button class="btn btn-sm btn-outline-primary" 
                                    onclick="previewFile('{{ $file['path'] }}')">
                                Preview
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/github.min.css">
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
<script>
function previewFile(path) {
    fetch(`/themes/{{ $theme->id }}/versions/{{ $version->id }}/file?path=${encodeURIComponent(path)}`)
        .then(response => response.json())
        .then(data => {
            const modal = new bootstrap.Modal(document.getElementById('filePreviewModal'));
            document.getElementById('filePreviewTitle').innerText = path;
            document.getElementById('filePreviewContent').innerText = data.content;
            hljs.highlightElement(document.getElementById('filePreviewContent'));
            modal.show();
        });
}
</script>
@endpush

<!-- File Preview Modal -->
<div class="modal fade" id="filePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filePreviewTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <pre class="m-0"><code id="filePreviewContent" class="hljs language-php"></code></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection
