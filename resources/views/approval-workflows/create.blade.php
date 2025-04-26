@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create New Approval Workflow</h1>

    <form method="POST" action="{{ route('approval-workflows.store') }}">
        @csrf

        <div class="card mb-4">
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Workflow Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3>Approval Steps</h3>
                <button type="button" class="btn btn-sm btn-primary" id="addStep">
                    Add Step
                </button>
            </div>
            <div class="card-body" id="stepsContainer">
                <div class="step mb-3 border p-3">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Approver Role</label>
                            <select class="form-select" name="steps[0][role_id]" required>
                                @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Order</label>
                            <input type="number" class="form-control" name="steps[0][order]" value="1" min="1" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-danger remove-step">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Create Workflow</button>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let stepCount = 1;
        
        document.getElementById('addStep').addEventListener('click', function() {
            const container = document.getElementById('stepsContainer');
            const newStep = document.createElement('div');
            newStep.className = 'step mb-3 border p-3';
            newStep.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Approver Role</label>
                        <select class="form-select" name="steps[${stepCount}][role_id]" required>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Order</label>
                        <input type="number" class="form-control" name="steps[${stepCount}][order]" value="${stepCount + 1}" min="1" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-sm btn-danger remove-step">
                            Remove
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(newStep);
            stepCount++;
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-step')) {
                e.target.closest('.step').remove();
            }
        });
    });
</script>
@endpush
@endsection