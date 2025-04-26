@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h2>Workflow: {{ $workflow->name }}</h2>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h4>Workflow Details</h4>
                            <p><strong>Status:</strong> {{ $workflow->status }}</p>
                            <p><strong>Created:</strong> {{ $workflow->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="{{ route('theme-approval-workflows.analytics', $workflow) }}" 
                               class="btn btn-primary">
                                View Analytics
                            </a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <h4>Approval Steps</h4>
                            @include('themes.approvals.partials.steps', ['steps' => $workflow->steps])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
