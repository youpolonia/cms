<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-volume-up mr-2"></i>Sound Settings
        </h5>
    </div>
    <div class="card-body">
        <div class="form-group">
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="notificationSoundsEnabled"
                    {{ $preferences->sound_enabled ? 'checked' : '' }}>
                <label class="custom-control-label" for="notificationSoundsEnabled">
                    Enable notification sounds
                </label>
                <small class="form-text text-muted">
                    Play sounds when new notifications arrive
                </small>
            </div>
        </div>

        <div class="form-group mt-4">
            <label class="mb-2">Test Notification Sounds</label>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-secondary test-sound" data-type="upcoming">
                    <i class="fas fa-clock mr-1"></i> Upcoming
                </button>
                <button type="button" class="btn btn-outline-secondary test-sound" data-type="conflict">
                    <i class="fas fa-exclamation-triangle mr-1"></i> Conflict
                </button>
                <button type="button" class="btn btn-outline-secondary test-sound" data-type="completed">
                    <i class="fas fa-check-circle mr-1"></i> Completed
                </button>
                <button type="button" class="btn btn-outline-secondary test-sound" data-type="changed">
                    <i class="fas fa-pencil-alt mr-1"></i> Changed
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script type="module">
import { notificationSounds } from '/js/notification-sounds.js';

$(document).ready(function() {
    // Toggle sound preference
    $('#notificationSoundsEnabled').change(function() {
        const enabled = $(this).is(':checked');
        notificationSounds.toggle(enabled);
        
        $.ajax({
            url: '{{ route("scheduling.notifications.preferences.update-sound") }}',
            method: 'POST',
            data: { enabled: enabled },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    });

    // Test sound buttons
    $('.test-sound').click(function() {
        const type = $(this).data('type');
        notificationSounds.play(type);
    });
});
</script>
@endpush