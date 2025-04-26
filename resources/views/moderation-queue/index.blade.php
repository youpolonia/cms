@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Content Moderation Queue</h1>

    <div class="card">
        <div class="card-body">
            @if($queueItems->isEmpty())
                <div class="alert alert-success">
                    No content awaiting moderation.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Content</th>
                                <th>Author</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($queueItems as $item)
                            <tr>
                                <td>
                                    <a href="{{ route('contents.show', $item->content) }}">
                                        {{ Str::limit($item->content->title, 50) }}
                                    </a>
                                </td>
                                <td>{{ $item->content->user->name }}</td>
                                <td>{{ $item->created_at->diffForHumans() }}</td>
                                <td class="d-flex gap-2">
                                    <form method="POST" action="{{ route('moderation-queue.approve', $item) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            Approve
                                        </button>
                                    </form>
                                    <button class="btn btn-sm btn-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#rejectModal{{ $item->id }}">
                                        Reject
                                    </button>
                                    <button class="btn btn-sm btn-warning"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deferModal{{ $item->id }}">
                                        Defer
                                    </button>
                                </td>
                            </tr>

                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal{{ $item->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('moderation-queue.reject', $item) }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Reject Content</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Reason for rejection</label>
                                                    <input type="text" name="reason" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Defer Modal -->
                            <div class="modal fade" id="deferModal{{ $item->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('moderation-queue.defer', $item) }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Defer Content</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Notes</label>
                                                    <input type="text" name="notes" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-warning">Confirm Deferral</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $queueItems->links() }}
            @endif
        </div>
    </div>
</div>
@endsection