@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Scheduling Notifications</h1>
        </div>
        <div class="col-md-4 text-right">
            <button class="btn btn-outline-primary" id="markAllAsReadBtn">
                <i class="fas fa-check-double"></i> Mark All as Read
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Your Notifications</h3>
                <span class="badge badge-primary" id="unreadBadge">{{ $unreadCount }} unread</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @forelse ($notifications as $notification)
                <a href="#" class="list-group-item list-group-item-action notification-item {{ is_null($notification->read_at) ? 'unread' : '' }}"
                    data-id="{{ $notification->id }}">
                    <div class="d-flex w-100 justify-content-between">
                        <div class="mr-3">
                            @switch($notification->type)
                                @case('upcoming')
                                    <span class="badge badge-warning"><i class="fas fa-clock"></i></span>
                                    @break
                                @case('conflict')
                                    <span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i></span>
                                    @break
                                @case('completed')
                                    <span class="badge badge-success"><i class="fas fa-check-circle"></i></span>
                                    @break
                                @case('changed')
                                    <span class="badge badge-info"><i class="fas fa-pencil-alt"></i></span>
                                    @break
                            @endswitch
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $notification->message }}</h6>
                            @if($notification->metadata)
                                <div class="metadata mt-2 small text-muted">
                                    @foreach($notification->metadata as $key => $value)
                                        @if(is_array($value))
                                            <div><strong>{{ ucfirst($key) }}:</strong> {{ implode(', ', $value) }}</div>
                                        @else
                                            <div><strong>{{ ucfirst($key) }}:</strong> {{ $value }}</div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="ml-3 text-right">
                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                            @if(is_null($notification->read_at))
                                <div class="mt-1">
                                    <span class="badge badge-pill badge-primary">New</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </a>
                @empty
                <div class="list-group-item">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-bell-slash fa-3x mb-3"></i>
                        <h4>No notifications yet</h4>
                        <p>You'll be notified about scheduling events here</p>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
        @if($notifications->hasPages())
        <div class="card-footer">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Mark as read when clicked
    $('.notification-item').click(function(e) {
        e.preventDefault();
        const notificationId = $(this).data('id');
        const $item = $(this);

        $.ajax({
            url: '/scheduling/notifications/' + notificationId + '/read',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
                $item.removeClass('unread');
                $item.find('.badge-pill').remove();
                updateUnreadCount();
            }
        });
    });

    // Mark all as read
    $('#markAllAsReadBtn').click(function() {
        $.ajax({
            url: '/scheduling/notifications/mark-all-read',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
                $('.notification-item').removeClass('unread');
                $('.badge-pill').remove();
                updateUnreadCount(0);
            }
        });
    });

    // Update unread count
    function updateUnreadCount(count) {
        if (typeof count === 'undefined') {
            $.get('/scheduling/notifications/unread-count', function(data) {
                $('#unreadBadge').text(data.count + ' unread');
            });
        } else {
            $('#unreadBadge').text(count + ' unread');
        }
    }

    // Poll for new notifications every 60 seconds
    setInterval(updateUnreadCount, 60000);
});
</script>
@endsection

<style>
.notification-item.unread {
    background-color: rgba(0, 123, 255, 0.05);
    border-left: 3px solid #007bff;
}
.notification-item:hover {
    background-color: #f8f9fa;
}
.metadata {
    background-color: #f8f9fa;
    padding: 5px;
    border-radius: 3px;
}
</style>