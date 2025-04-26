@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Version #{{ $version->version_number }} of {{ $content->title }}</h1>
    
    <div class="card mb-4">
        <div class="card-header">
            Version Details
        </div>
        <div class="card-body">
            <p><strong>Status:</strong>
                <span class="badge bg-{{ $version->isApproved() ? 'success' : ($version->isRejected() ? 'danger' : 'warning') }}">
                    {{ $version->approval_status }}
                </span>
                @if($version->is_restored)
                    <span class="badge bg-info ms-2">Restored</span>
                @endif
            </p>
            <p><strong>Created:</strong> {{ $version->created_at->format('Y-m-d H:i') }}</p>
            <p><strong>By:</strong> {{ $version->user->name }}</p>
            <p><strong>Description:</strong> {{ $version->change_description }}</p>

            @if($version->is_restored)
                <div class="mt-3 pt-3 border-top">
                    <h5>Restoration Details</h5>
                    <p><strong>Restored At:</strong> {{ $version->restored_at->format('Y-m-d H:i') }}</p>
                    <p><strong>Restored By:</strong> {{ $version->restoredBy->name ?? 'System' }}</p>
                    @if($version->restoration_reason)
                        <p><strong>Reason:</strong> {{ $version->restoration_reason }}</p>
                    @endif
                    @if($version->restoredFromVersion)
                        <p><strong>Restored From:</strong>
                            <a href="{{ route('content.versions.show', [$content, $version->restoredFromVersion]) }}">
                                Version #{{ $version->restoredFromVersion->version_number }}
                            </a>
                        </p>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            Content
        </div>
        <div class="card-body">
            <h3>{{ $version->title }}</h3>
            <div class="content-body">
                {!! $version->content !!}
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            Scheduling
        </div>
        <div class="card-body">
            @livewire('content-scheduling', [
                'contentId' => $content->id,
                'versionId' => $version->id
            ])
        </div>
    </div>

    @can('moderate', $version)
    <div class="card mb-4">
        <div class="card-header">
            Moderation Actions
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('content-versions.moderate', $version) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Action</label>
                    <select name="action" class="form-select" required>
                        <option value="approve">Approve</option>
                        <option value="request_changes">Request Changes</option>
                        <option value="reject">Reject</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3 changes-requested" style="display: none;">
                    <label class="form-label">Changes Requested (one per line)</label>
                    <textarea name="changes_requested" class="form-control" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Moderation</button>
            </form>
        </div>
    </div>
    @endcan

    <div class="mt-4">
        <a href="{{ route('content.versions.index', $content) }}" class="btn btn-secondary">
            Back to Versions
        </a>
        <a href="{{ route('content-versions.moderation-history', $version) }}" class="btn btn-info">
            View Moderation History
        </a>
        @can('restore', $version)
        @livewire('restore-version-button', [
            'content' => $content,
            'version' => $version
        ])
        @endcan
    </div>
</div>

@push('scripts')
<script>
    document.querySelector('select[name="action"]').addEventListener('change', function() {
        const changesDiv = document.querySelector('.changes-requested');
        changesDiv.style.display = this.value === 'request_changes' ? 'block' : 'none';
    });
</script>
@endpush
@endsection
