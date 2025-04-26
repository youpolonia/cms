@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Approval Required</div>

                    <div class="card-body">
                        <div class="alert alert-info">
                            <h5 class="alert-heading">Approval Required</h5>
                            <p>
                                Your content "{{ $content->title }}" (Version #{{ $content->latestVersion->version_number }}) 
                                has been submitted for approval.
                            </p>
                            @if($content->branch)
                                <p class="mb-0">Branch: <span class="badge bg-secondary">{{ $content->branch->name }}</span></p>
                            @endif
                        </div>

                        <div class="approval-details">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>Approval Workflow: {{ $workflow->name }}</h5>
                                <span class="badge bg-primary">
                                    Estimated Completion: {{ $workflow->estimated_completion }}
                                </span>
                            </div>
                            
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="progress mb-3" style="height: 10px;">
                                        <div class="progress-bar bg-success" 
                                             style="width: {{ $workflow->completion_percentage }}%"
                                             role="progressbar">
                                        </div>
                                    </div>
                                    <ul class="list-group">
                                        @foreach($workflow->steps as $step)
                                            <li class="list-group-item {{ $step->is_complete ? 'list-group-item-success' : '' }}">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong>{{ $step->name }}</strong>
                                                        @if(!$step->is_complete && $step->is_current)
                                                            <span class="badge bg-warning text-dark ms-2">Current Step</span>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        @if($step->is_complete)
                                                            <small class="text-muted">
                                                                Approved by {{ $step->approvedBy->name }} on {{ $step->updated_at->format('m/d/Y') }}
                                                            </small>
                                                        @else
                                                            <small class="text-muted">
                                                                Approver: {{ $step->approver->name }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                Version Comparison
                            </div>
                            <div class="card-body">
                                <a href="{{ route('content.versions.compare', [
                                    'content' => $content,
                                    'version1' => $content->latestVersion->id,
                                    'version2' => $content->publishedVersion ? $content->publishedVersion->id : null
                                ]) }}" 
                                   class="btn btn-outline-primary">
                                    Compare with Published Version
                                </a>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('content.index') }}" class="btn btn-primary">
                                Back to Content List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
