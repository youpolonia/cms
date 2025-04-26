@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Version Comparison</h1>
        <div>
            <a href="{{ route('contents.show', $version1->content) }}" class="btn btn-outline-secondary">
                Back to Content
            </a>
            @if($version1->is_current)
                <button class="btn btn-primary" disabled>Current Version</button>
            @else
                <form action="{{ route('content-versions.restore', $version1) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        Restore This Version
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <h5>Version #{{ $version1->version_number }}</h5>
                    <small class="text-muted">
                        {{ $version1->created_at->format('M d, Y H:i') }} by {{ $version1->creator->name }}
                    </small>
                </div>
                <div class="col-md-6">
                    <h5>Version #{{ $version2->version_number }}</h5>
                    <small class="text-muted">
                        {{ $version2->created_at->format('M d, Y H:i') }} by {{ $version2->creator->name }}
                    </small>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="diff-container">
                {!! $diff['html_diff'] !!}
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Change Statistics</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="stat-card">
                        <span class="stat-number text-success">{{ $diff['stats']['added'] }}</span>
                        <span class="stat-label">Lines Added</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <span class="stat-number text-danger">{{ $diff['stats']['removed'] }}</span>
                        <span class="stat-label">Lines Removed</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <span class="stat-number text-warning">{{ $diff['stats']['changed'] }}</span>
                        <span class="stat-label">Lines Changed</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .diff-container {
        overflow-x: auto;
    }
    .stat-card {
        text-align: center;
        padding: 15px;
    }
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        display: block;
    }
    .stat-label {
        font-size: 1rem;
        color: #6c757d;
    }
</style>
@endsection