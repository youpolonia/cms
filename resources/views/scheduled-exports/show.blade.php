@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Export: {{ $export->name }}</h1>
        <div>
            <a href="{{ route('scheduled-exports.index') }}" class="btn btn-outline-secondary">
                Back to List
            </a>
            <a href="{{ route('scheduled-exports.edit', $export) }}" class="btn btn-primary">
                Edit
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Details</div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Frequency</dt>
                        <dd class="col-sm-8">{{ ucfirst($export->frequency) }}</dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $export->status === 'active' ? 'success' : 'warning' }}">
                                {{ ucfirst($export->status) }}
                            </span>
                        </dd>

                        <dt class="col-sm-4">Next Run</dt>
                        <dd class="col-sm-8">{{ $export->next_run_at->format('Y-m-d H:i') }}</dd>

                        <dt class="col-sm-4">Start Date</dt>
                        <dd class="col-sm-8">{{ $export->start_date->format('Y-m-d H:i') }}</dd>

                        @if($export->end_date)
                        <dt class="col-sm-4">End Date</dt>
                        <dd class="col-sm-8">{{ $export->end_date->format('Y-m-d H:i') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Actions</div>
                <div class="card-body">
                    <form action="{{ route('scheduled-exports.run-now', $export) }}" method="POST" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            Run Export Now
                        </button>
                    </form>

                    <form action="{{ route('scheduled-exports.destroy', $export) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure?')">
                            Delete Export
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Export History</div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Run Time</th>
                        <th>Status</th>
                        <th>File</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($export->runs as $run)
                        <tr>
                            <td>{{ $run->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <span class="badge bg-{{ $run->status === 'completed' ? 'success' : 'danger' }}">
                                    {{ ucfirst($run->status) }}
                                </span>
                            </td>
                            <td>
                                @if($run->status === 'completed' && $run->file_path)
                                    <a href="{{ Storage::url($run->file_path) }}" target="_blank">
                                        Download
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection