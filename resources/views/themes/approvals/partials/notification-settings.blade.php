<div class="bg-white rounded-lg shadow overflow-hidden mb-6">
    <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Notification Settings</h2>
        
        <form action="{{ route('theme-approvals.workflows.notifications.update', $workflow) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Notification Events
                    </label>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <input type="checkbox" name="notify_on_submit" id="notify_on_submit" 
                                   {{ $workflow->notify_on_submit ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="notify_on_submit" class="ml-2 block text-sm text-gray-700">
                                When theme is submitted for approval
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="notify_on_step_approval" id="notify_on_step_approval" 
                                   {{ $workflow->notify_on_step_approval ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="notify_on_step_approval" class="ml-2 block text-sm text-gray-700">
                                When a step is approved
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="notify_on_step_rejection" id="notify_on_step_rejection" 
                                   {{ $workflow->notify_on_step_rejection ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="notify_on_step_rejection" class="ml-2 block text-sm text-gray-700">
                                When a step is rejected
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="notify_on_completion" id="notify_on_completion" 
                                   {{ $workflow->notify_on_completion ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="notify_on_completion" class="ml-2 block text-sm text-gray-700">
                                When workflow is completed
                            </label>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="notification_recipients" class="block text-sm font-medium text-gray-700">
                        Additional Recipients (comma separated emails)
                    </label>
                    <input type="text" name="notification_recipients" id="notification_recipients"
                           value="{{ old('notification_recipients', $workflow->notification_recipients) }}"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Save Notification Settings
                </button>
            </div>
        </form>

        <div class="mt-8 pt-8 border-t border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Your Notification Preferences</h3>
            <p class="text-sm text-gray-500 mb-4">Configure how you receive notifications for theme approvals</p>
            @livewire('theme-approval-notification-settings')
        </div>
    </div>
</div>
