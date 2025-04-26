@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Content Management</h1>
        <a href="{{ route('contents.create') }}" class="btn btn-primary">
            Create New Content
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($contents as $content)
                    <tr>
                        <td>{{ $content->title }}</td>
                        <td>{{ ucfirst($content->content_type) }}</td>
                        <td>{{ $content->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('contents.show', $content) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('contents.edit', $content) }}" class="btn btn-sm btn-primary">Edit</a>
                            <form action="{{ route('contents.destroy', $content) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $contents->links() }}
        </div>
    </div>
</div>
@endsection