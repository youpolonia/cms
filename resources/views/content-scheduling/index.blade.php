@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Content Scheduling</h1>

    <ul class="nav nav-tabs mb-4" id="schedulingTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" 
                    data-bs-target="#upcoming" type="button" role="tab">
                Upcoming ({{ $scheduledContents->total() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="expired-tab" data-bs-toggle="tab" 
                    data-bs-target="#expired" type="button" role="tab">
                Expired ({{ $expiredContents->total() }})
            </button>
        </li>
        <li class="nav-item ms-auto">
            <a href="{{ route('content-scheduling.calendar') }}" class="btn btn-sm btn-primary">
                Calendar View
            </a>
        </li>
    </ul>

    <div class="tab-content" id="schedulingTabsContent">
        <div class="tab-pane fade show active" id="upcoming" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    @if($scheduledContents->isEmpty())
                        <div class="alert alert-info">
                            No content is currently scheduled for publishing.
                        </div>
                    @else
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Content</th>
                                    <th>Category</th>
                                    <th>Scheduled Publish</th>
                                    <th>Scheduled Expire</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($scheduledContents as $content)
                                    <tr>
                                        <td>
                                            <a href="{{ route('content.show', $content) }}">
                                                {{ $content->title }}
                                            </a>
                                        </td>
                                        <td>{{ $content->category?->name ?? 'None' }}</td>
                                        <td>{{ $content->publish_at->format('Y-m-d H:i') }}</td>
                                        <td>{{ $content->expire_at?->format('Y-m-d H:i') ?? 'Never' }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('content.unschedule', $content) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-danger">
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

        <div class="tab-pane fade" id="expired" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    @if($expiredContents->isEmpty())
                        <div class="alert alert-info">
                            No content has expired.
                        </div>
                    @else
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Content</th>
                                    <th>Category</th>
                                    <th>Published</th>
                                    <th>Expired</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expiredContents as $content)
                                    <tr>
                                        <td>
                                            <a href="{{ route('content.show', $content) }}">
                                                {{ $content->title }}
                                            </a>
                                        </td>
                                        <td>{{ $content->category?->name ?? 'None' }}</td>
                                        <td>{{ $content->publish_at->format('Y-m-d H:i') }}</td>
                                        <td>{{ $content->expire_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('content.schedule', $content) }}">
                                                @csrf
                                                <input type="hidden" name="publish_at" value="{{ now()->format('Y-m-d\TH:i') }}">
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    Republish
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $expiredContents->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection