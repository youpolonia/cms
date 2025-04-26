@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Moderation History for Version #{{ $version->version_number }}</h2>
    
    <div class="card mb-4">
        <div class="card-header">
            <h3>Current Status: 
                <span class="badge bg-{{ $version->isApproved() ? 'success' : ($version->isRejected() ? 'danger' : 'warning') }}">
                    {{ $version->approval_status }}
                </span>
            </h3>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Moderation Actions</h3>
        </div>
        <div class="card-body">
            @foreach($moderations as $moderation)
                <div class="mb-4 p-3 border rounded">
                    <div class="d-flex justify-content-between">
                        <h5>
                            {{ ucfirst($moderation->action) }}
                            @if($moderation->action === 'request_changes')
                                (Changes Requested)
                            @endif
                        </h5>
                        <small class="text-muted">
                            {{ $moderation->created_at->format('M j, Y g:i a') }}
                        </small>
                    </div>
                    <p class="mb-1"><strong>Moderator:</strong> {{ $moderation->moderator->name }}</p>
                    @if($moderation->notes)
                        <p class="mb-1"><strong>Notes:</strong> {{ $moderation->notes }}</p>
                    @endif
                    @if($moderation->changes_requested)
                        <div class="mt-2">
                            <h6>Changes Requested:</h6>
                            <ul>
                                @foreach($moderation->changes_requested as $change)
                                    <li>{{ $change }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection