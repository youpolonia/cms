@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Add Step to Workflow: {{ $workflow->name }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('workflows.steps.store', $workflow) }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Step Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="order" class="form-label">Step Order</label>
                            <input type="number" class="form-control" id="order" name="order" min="1" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Add Step</button>
                        <a href="{{ route('workflows.show', $workflow) }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection