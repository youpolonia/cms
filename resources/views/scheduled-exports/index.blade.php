@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Scheduled Exports</h1>
        <a href="{{ route('scheduled-exports.create') }}" class="btn btn-primary">
            Schedule New Export
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Frequency</th>
                        <th>Next Run</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($exports as $export)
                        <tr>
                            <td>{{ $export->name }}</td>
                            <td>{{ ucfirst($export->frequency) }}</td>
                            <td>{{ $export->next_run_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <span class="badge bg-{{ $export->status === 'active' ? 'success' : 'warning' }}">
                                    {{ ucfirst($export->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('scheduled-exports.show', $export) }}" class="btn btn-sm btn-info">
                                    View
                                </a>
                                <a href="{{ route('scheduled-exports.edit', $export) }}" class="btn btn-sm btn-primary">
                                    Edit
                                </a>
                                <form action="{{ route('scheduled-exports.destroy', $export) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $exports->links() }}
    </div>
</div>
@endsection