@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ $workflow->name }}</h1>
        <div>
            <a href="{{ route('approval-workflows.index') }}" class="btn btn-secondary">
                Back to Workflows
            </a>
        </div>
    </div>

    @if($workflow->description)
        <div class="card mb-4">
            <div class="card-body">
                <p class="mb-0">{{ $workflow->description }}</p>
            </div>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <h3>Approval Steps</h3>
        </div>
        <div class="card-body">
            <div class="timeline">
                @foreach($workflow->steps as $step)
                    <div class="timeline-step {{ $loop->first ? 'timeline-step-first' : '' }} {{ $loop->last ? 'timeline-step-last' : '' }}">
                        <div class="timeline-step-header">
                            <h4>{{ $step->name }}</h4>
                            <span class="badge bg-primary">Step {{ $loop->iteration }}</span>
                        </div>
                        <div class="timeline-step-body">
                            <h5>Approvers</h5>
                            <ul>
                                @foreach($step->approvers as $approver)
                                    <li>{{ $approver->name }}</li>
                                @endforeach
                            </ul>

                            @if($step->decisions->count() > 0)
                                <h5>Recent Decisions</h5>
                                <ul>
                                    @foreach($step->decisions->take(3) as $decision)
                                        <li>
                                            {{ $decision->content->title }} - 
                                            {{ ucfirst($decision->status) }} by 
                                            {{ $decision->user->name }} on 
                                            {{ $decision->created_at->format('Y-m-d') }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Associated Content ({{ $workflow->contents->count() }})</h3>
        </div>
        <div class="card-body">
            @if($workflow->contents->isEmpty())
                <div class="alert alert-info">
                    No content is currently using this workflow
                </div>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>Content</th>
                            <th>Current Status</th>
                            <th>Current Step</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($workflow->contents as $content)
                            <tr>
                                <td>
                                    <a href="{{ route('content.show', $content) }}">
                                        {{ $content->title }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $content->approval_status === 'approved' ? 'success' : ($content->approval_status === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($content->approval_status) }}
                                    </span>
                                </td>
                                <td>
                                    {{ $content->currentApprovalStep?->name ?? 'Not started' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 50px;
    }
    .timeline-step {
        position: relative;
        padding-bottom: 30px;
    }
    .timeline-step:before {
        content: '';
        position: absolute;
        left: -30px;
        top: 0;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #0d6efd;
        border: 3px solid white;
    }
    .timeline-step:after {
        content: '';
        position: absolute;
        left: -22px;
        top: 20px;
        width: 4px;
        height: calc(100% - 20px);
        background: #dee2e6;
    }
    .timeline-step-last:after {
        display: none;
    }
    .timeline-step-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .timeline-step-body {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
    }
</style>
@endpush