@props(['user'])

<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-medium">Theme Rollback Notifications</h3>
            <p class="text-sm text-gray-500">
                Configure how you receive notifications about theme rollback events
            </p>
        </div>
    </div>

    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-sm font-medium">Rollback Requests</h4>
                <p class="text-sm text-gray-500">
                    When a rollback is initiated for a theme version
                </p>
            </div>
            <x-toggle 
                name="rollback_request_enabled" 
                :checked="$user->notificationPreferences->rollback_request_enabled ?? true" 
            />
        </div>

        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-sm font-medium">Rollback Progress</h4>
                <p class="text-sm text-gray-500">
                    When a rollback is being processed
                </p>
            </div>
            <x-toggle 
                name="rollback_progress_enabled" 
                :checked="$user->notificationPreferences->rollback_progress_enabled ?? true" 
            />
        </div>

        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-sm font-medium">Rollback Completion</h4>
                <p class="text-sm text-gray-500">
                    When a rollback is successfully completed
                </p>
            </div>
            <x-toggle 
                name="rollback_completion_enabled" 
                :checked="$user->notificationPreferences->rollback_completion_enabled ?? true" 
            />
        </div>

        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-sm font-medium">Rollback Failures</h4>
                <p class="text-sm text-gray-500">
                    When a rollback fails to complete
                </p>
            </div>
            <x-toggle 
                name="rollback_failure_enabled" 
                :checked="$user->notificationPreferences->rollback_failure_enabled ?? true" 
            />
        </div>

        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-sm font-medium">Email Notifications</h4>
                <p class="text-sm text-gray-500">
                    Receive rollback notifications via email
                </p>
            </div>
            <x-toggle 
                name="rollback_email_enabled" 
                :checked="$user->notificationPreferences->rollback_email_enabled ?? true" 
            />
        </div>

        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-sm font-medium">In-App Notifications</h4>
                <p class="text-sm text-gray-500">
                    Receive rollback notifications in the application
                </p>
            </div>
            <x-toggle 
                name="rollback_in_app_enabled" 
                :checked="$user->notificationPreferences->rollback_in_app_enabled ?? true" 
            />
        </div>
    </div>
</div>
