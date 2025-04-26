@props(['user'])

<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-medium">Theme Approval Notifications</h3>
            <p class="text-sm text-gray-500">
                Configure how you receive notifications about theme approval requests and status updates
            </p>
        </div>
    </div>

    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-sm font-medium">Approval Requests</h4>
                <p class="text-sm text-gray-500">
                    When you're assigned to approve a theme version
                </p>
            </div>
            <x-toggle 
                name="approval_request_enabled" 
                :checked="$user->notificationPreferences->approval_request_enabled ?? true" 
            />
        </div>

        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-sm font-medium">Approval Status Updates</h4>
                <p class="text-sm text-gray-500">
                    When a theme you submitted is approved/rejected
                </p>
            </div>
            <x-toggle 
                name="approval_status_enabled" 
                :checked="$user->notificationPreferences->approval_status_enabled ?? true" 
            />
        </div>

        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-sm font-medium">Approval Completion</h4>
                <p class="text-sm text-gray-500">
                    When the entire approval workflow is completed
                </p>
            </div>
            <x-toggle 
                name="approval_completion_enabled" 
                :checked="$user->notificationPreferences->approval_completion_enabled ?? true" 
            />
        </div>

        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-sm font-medium">Email Notifications</h4>
                <p class="text-sm text-gray-500">
                    Receive notifications via email
                </p>
            </div>
            <x-toggle 
                name="approval_email_enabled" 
                :checked="$user->notificationPreferences->approval_email_enabled ?? true" 
            />
        </div>

        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-sm font-medium">In-App Notifications</h4>
                <p class="text-sm text-gray-500">
                    Receive notifications in the application
                </p>
            </div>
            <x-toggle 
                name="approval_in_app_enabled" 
                :checked="$user->notificationPreferences->approval_in_app_enabled ?? true" 
            />
        </div>
    </div>
</div>
