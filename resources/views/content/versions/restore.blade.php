@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Restore Version #{{ $version->id }}</h1>
    
    <div class="alert alert-warning">
        <h5 class="alert-heading">Important Restoration Notes</h5>
        <ul class="mb-1">
            <li>Restoring this version will overwrite the current content</li>
            <li>A new version will be created with the restored content</li>
            <li>The original version will be marked as restored for tracking</li>
            <li>All changes will be logged in the version history</li>
        </ul>
    </div>

    @if($version->is_restored)
        <div class="alert alert-info">
            <strong>Note:</strong> This version was previously restored on
            {{ $version->restored_at->format('Y-m-d H:i') }} by
            {{ $version->restoredBy->name ?? 'system' }}
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            Version Details
        </div>
        <div class="card-body">
            <p><strong>Created:</strong> {{ $version->created_at->format('Y-m-d H:i') }}</p>
            <p><strong>By:</strong> {{ $version->user->name }}</p>
            @if($version->approval_status)
                <p><strong>Status:</strong> {{ ucfirst($version->approval_status) }}</p>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            Comparison Statistics
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <h5>Content Changes</h5>
                    <p>Total Changes: {{ $stats->change_count }}</p>
                    <p>Additions: <span class="text-success">{{ $stats->additions }}</span></p>
                    <p>Deletions: <span class="text-danger">{{ $stats->deletions }}</span></p>
                </div>
                <div class="col-md-4">
                    <h5>View Counts</h5>
                    <p>Version Views: {{ $stats->version_views }}</p>
                    <p>Current Views: {{ $stats->current_views }}</p>
                </div>
                <div class="col-md-4">
                    <h5>Comparison</h5>
                    <p>Similarity: {{ $stats->similarity }}%</p>
                    <p>Changed Lines: {{ $stats->changed_lines }}</p>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('content.versions.restore', [$content, $version]) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="reason">Restoration Reason *</label>
            <textarea id="reason" name="reason" class="form-control"
                      required minlength="10" maxlength="500" rows="3"
                      placeholder="Explain why you're restoring this version (required)"></textarea>
            <small class="form-text text-muted">
                Minimum 10 characters, maximum 500 characters. This will be recorded in the version history.
            </small>
        </div>

        <div class="form-group form-check mb-4">
            <input type="checkbox" class="form-check-input" id="create_new_version"
                   name="create_new_version" value="1" checked>
            <label class="form-check-label" for="create_new_version">
                Create backup version of current content before restoring
            </label>
            <small class="form-text text-muted">
                Uncheck only if you're certain you don't need a backup
            </small>
        </div>

        <div class="form-group mt-4">
            <button type="submit" class="btn btn-primary">
                Confirm Restoration
            </button>
            <a href="{{ route('content.versions.index', $content) }}" class="btn btn-secondary">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection