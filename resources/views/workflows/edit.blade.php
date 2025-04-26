@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Edit Workflow</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('workflows.update', $workflow) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Workflow Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="{{ old('name', $workflow->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3">
                                {{ old('description', $workflow->description) }}
                            </textarea>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
                                   {{ $workflow->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Workflow</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection