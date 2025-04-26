@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4>Content Management</h4>
                        <a href="{{ route('admin.content.create') }}" class="btn btn-primary">
                            Create New Content
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Author</th>
                                <th>Categories</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contents as $content)
                            <tr>
                                <td>{{ $content->title }}</td>
                                <td>{{ ucfirst($content->content_type) }}</td>
                                <td>{{ $content->user->name }}</td>
                                <td>
                                    @foreach($content->categories as $category)
                                        <span class="badge bg-primary">{{ $category->name }}</span>
                                    @endforeach
                                </td>
                                <td>{{ $content->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.content.edit', $content->id) }}" 
                                       class="btn btn-sm btn-outline-primary">Edit</a>
                                    <a href="{{ route('admin.content.show', $content->id) }}" 
                                       class="btn btn-sm btn-outline-secondary">View</a>
                                    <form action="{{ route('admin.content.destroy', $content->id) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Are you sure?')">Delete</button>
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
    </div>
</div>
@endsection