@if(auth()->check())
<div class="dropdown ml-2">
    <button class="btn btn-light dropdown-toggle position-relative" type="button" 
            id="notificationDropdown" data-toggle="dropdown" aria-haspopup="true" 
            aria-expanded="false">
        <i class="fas fa-bell"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
              id="notificationBadge" style="font-size: 0.6rem;">
            {{ auth()->user()->unreadSchedulingNotifications()->count() }}
        </span>
    </button>
    <div class="dropdown-menu dropdown-menu-right p-0" aria-labelledby="notificationDropdown" 
         style="width: 350px; max-height: 400px; overflow-y: auto;">
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
            <h6 class="mb-0">Notifications</h6>
            <a href="{{ route('scheduling.notifications.index') }}" class="small">View All</a>
        </div>
        
        <div id="notificationList">
            @foreach(auth()->user()->schedulingNotifications()->latest()->take(5)->get() as $notification)
            <a href="#" class="dropdown-item notification-item {{ $notification->read_at ? '' : 'unread' }}"
               data-id="{{ $notification->id }}">
                <div class="d-flex">
                    <div class="mr-2">
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
                        <div class="small">{{ Str::limit($notification->message, 50) }}</div>
                        <div class="text-muted small">{{ $notification->created_at->diffForHumans() }}</div>
                    </div>
                    @if(!$notification->read_at)
                        <div class="ml-2">
                            <span class="badge badge-pill badge-primary">New</span>
                        </div>
                    @endif
                </div>
            </a>
            @endforeach
            
            @if(auth()->user()->schedulingNotifications()->count() === 0)
            <div class="dropdown-item text-center text-muted py-3">
                <i class="fas fa-bell-slash fa-2x mb-2"></i>
                <div>No notifications yet</div>
            </div>
            @endif
        </div>
        
        <div class="dropdown-divider"></div>
        <a href="{{ route('scheduling.notifications.preferences') }}" class="dropdown-item text-center small">
            <i class="fas fa-cog mr-1"></i> Notification Preferences
        </a>
    </div>
</div>

@push('scripts')
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
                updateNotificationBadge();
            }
        });
    });

    // Update notification badge count
    function updateNotificationBadge() {
        $.get('/scheduling/notifications/unread-count', function(data) {
            const badge = $('#notificationBadge');
            badge.text(data.count);
            if (data.count === 0) {
                badge.addClass('d-none');
            } else {
                badge.removeClass('d-none');
            }
        });
    }

    // Poll for new notifications every 30 seconds
    setInterval(updateNotificationBadge, 30000);
});
</script>
@endpush

<style>
.notification-item.unread {
    background-color: rgba(0, 123, 255, 0.05);
}
.notification-item:hover {
    background-color: #f8f9fa;
}
</style>
@endif