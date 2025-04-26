@props(['user'])

<div class="space-y-4">
    <h3 class="text-lg font-medium">Content Approval Notifications</h3>
    
    <div class="space-y-2">
        <div class="flex items-center">
            <input 
                type="checkbox" 
                id="step_started_notification" 
                name="step_started_notification"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                @if($user->notification_preferences['content_approval']['step_started'] ?? true) checked @endif
            >
            <label for="step_started_notification" class="ml-2">When content reaches my approval step</label>
        </div>

        <div class="flex items-center">
            <input 
                type="checkbox" 
                id="workflow_completed_notification" 
                name="workflow_completed_notification"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                @if($user->notification_preferences['content_approval']['workflow_completed'] ?? true) checked @endif
            >
            <label for="workflow_completed_notification" class="ml-2">When content is fully approved</label>
        </div>

        <div class="flex items-center">
            <input 
                type="checkbox" 
                id="content_rejected_notification" 
                name="content_rejected_notification"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                @if($user->notification_preferences['content_approval']['content_rejected'] ?? true) checked @endif
            >
            <label for="content_rejected_notification" class="ml-2">When my content is rejected</label>
        </div>
    </div>
</div>
