@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Scheduled Content</h1>

    <div class="card">
        <div class="card-body">
            @if($scheduledContents->isEmpty())
                <div class="alert alert-info">
                    No content currently scheduled.
                </div>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>Content</th>
                            <th>Publish Date</th>
                            <th>Expiration Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($scheduledContents as $content)
                        <tr>
                            <td>{{ $content->title }}</td>
                            <td>{{ $content->publish_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $content->expire_at?->format('Y-m-d H:i') ?? 'Never' }}</td>
                            <td>
                                @if($content->publish_at->isPast())
                                    <span class="badge bg-success">Published</span>
                                @else
                                    <span class="badge bg-warning">Scheduled</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('contents.show', $content) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    View
                                </a>
                                <form action="{{ route('content-schedule.destroy', $content) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Remove schedule for this content?')">
                                        Unschedule
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $scheduledContents->links() }}
            @endif
        </div>
    </div>
</div>
@endsection