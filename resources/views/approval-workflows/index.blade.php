@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Approval Workflows</h1>
        <a href="{{ route('approval-workflows.create') }}" class="btn btn-primary">
            Create New Workflow
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            @if($workflows->isEmpty())
                <div class="alert alert-info">
                    No approval workflows found.
                </div>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Steps</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($workflows as $workflow)
                        <tr>
                            <td>{{ $workflow->name }}</td>
                            <td>
                                <ol>
                                    @foreach($workflow->steps->sortBy('order') as $step)
                                    <li>{{ $step->role->name }}</li>
                                    @endforeach
                                </ol>
                            </td>
                            <td>
                                <a href="#" class="btn btn-sm btn-outline-secondary">
                                    Edit
                                </a>
                                <form action="#" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        Delete
                                    </button>
                                </form>
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