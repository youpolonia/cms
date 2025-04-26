@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Error Resolution Workflows</h1>
        <a href="{{ route('workflows.create') }}" class="btn btn-primary">
            Create New Workflow
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Steps</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($workflows as $workflow)
                    <tr>
                        <td>{{ $workflow->name }}</td>
                        <td>{{ $workflow->error_category_id }}</td>
                        <td>{{ $workflow->steps->count() }}</td>
                        <td>
                            <span class="badge bg-{{ $workflow->is_active ? 'success' : 'secondary' }}">
                                {{ $workflow->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('workflows.show', $workflow) }}" class="btn btn-sm btn-info">
                                View
                            </a>
                            <a href="{{ route('workflows.edit', $workflow) }}" class="btn btn-sm btn-warning">
                                Edit
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection