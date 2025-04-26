@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- File Browser Sidebar -->
        <div class="col-md-3 bg-light p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>{{ $theme->name }}</h4>
                <div>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newFileModal">
                        New File
                    </button>
                    <a href="{{ route('themes.show', $theme) }}" class="btn btn-sm btn-outline-secondary">
                        Back to Theme
                    </a>
                </div>
            </div>

            <!-- New File Modal -->
            <div class="modal fade" id="newFileModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('themes.create-file', $theme) }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Create New File</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="filePath" class="form-label">File Path</label>
                                    <input type="text" class="form-control" id="filePath" name="file_path" required 
                                        placeholder="e.g. views/layouts/custom.blade.php">
                                </div>
                                <div class="mb-3">
                                    <label for="fileContent" class="form-label">Initial Content</label>
                                    <textarea class="form-control" id="fileContent" name="content" rows="5"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Create File</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="list-group">
                @foreach($files as $file)
                <a href="{{ route('themes.edit-file', ['theme' => $theme, 'file' => $file['path']]) }}"
                   class="list-group-item list-group-item-action {{ $currentFile === $file['path'] ? 'active' : '' }}">
                    <div class="d-flex justify-content-between">
                        <span>{{ $file['path'] }}</span>
                        <small class="text-muted">{{ $file['type'] }}</small>
                    </div>
                </a>
                @endforeach
            </div>
        </div>

        <!-- Editor Area -->
        <div class="col-md-9 p-0">
            @if($currentFile)
            <div class="d-flex justify-content-between align-items-center bg-dark text-white p-2">
                <div>{{ $currentFile }}</div>
                <form action="{{ route('themes.update-file', $theme) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="file_path" value="{{ $currentFile }}">
                    <button type="submit" class="btn btn-sm btn-success">Save Changes</button>
                </form>
            </div>

            <form id="editor-form" action="{{ route('themes.update-file', $theme) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="file_path" value="{{ $currentFile }}">
                <textarea id="code-editor" name="content" class="form-control" style="height: 80vh;">{{ $fileContent }}</textarea>
            </form>

            @else
            <div class="d-flex align-items-center justify-content-center" style="height: 80vh;">
                <div class="text-center">
                    <h4>Select a file to edit</h4>
                    <p class="text-muted">Choose a file from the sidebar to begin editing</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editor = CodeMirror.fromTextArea(document.getElementById('code-editor'), {
            lineNumbers: true,
            mode: '{{ $fileType }}',
            theme: 'default',
            indentUnit: 4,
            tabSize: 4,
            lineWrapping: true
        });

        document.getElementById('editor-form').addEventListener('submit', function() {
            document.getElementById('code-editor').value = editor.getValue();
        });
    });
</script>
@endpush
