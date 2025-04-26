@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>{{ $workflow->name }}</h2>
            <span class="badge bg-{{ $workflow->is_active ? 'success' : 'secondary' }}">
                {{ $workflow->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>

        <div class="card-body">
            <p class="card-text">{{ $workflow->description }}</p>
            
            <h4 class="mt-4">Steps</h4>
            @livewire('step-manager', ['workflow' => $workflow])

            <div class="mt-4">
                <a href="{{ route('workflows.steps.create', $workflow) }}" 
                   class="btn btn-primary">
                    Add New Step
                </a>
                <a href="{{ route('workflows.edit', $workflow) }}" class="btn btn-warning">
                    Edit Workflow
                </a>
                <a href="{{ route('workflows.index') }}" class="btn btn-secondary">
                    Back to List
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/gh/livewire/sortable@v0.x.x/dist/livewire-sortable.js"></script>
@endpush