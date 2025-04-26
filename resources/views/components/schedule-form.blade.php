<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Publish Schedule</label>
        <div class="mt-1 flex items-center space-x-4">
            <input type="datetime-local" name="publish_at" 
                   value="{{ old('publish_at', $content->publish_at ?? '') }}"
                   class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm">
            <div class="flex items-center">
                <input id="publish_immediately" name="publish_immediately" type="checkbox" 
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="publish_immediately" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                    Publish immediately
                </label>
            </div>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expiration</label>
        <div class="mt-1 flex items-center space-x-4">
            <input type="datetime-local" name="expire_at" 
                   value="{{ old('expire_at', $content->expire_at ?? '') }}"
                   class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm">
            <div class="flex items-center">
                <input id="never_expire" name="never_expire" type="checkbox" 
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="never_expire" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                    Never expire
                </label>
            </div>
        </div>
    </div>

    @if($content->recurring_schedule)
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Recurring Schedule</label>
        <div class="mt-1">
            <select name="recurring_schedule" class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm">
                <option value="">None</option>
                <option value="daily" {{ old('recurring_schedule', $content->recurring_schedule) === 'daily' ? 'selected' : '' }}>Daily</option>
                <option value="weekly" {{ old('recurring_schedule', $content->recurring_schedule) === 'weekly' ? 'selected' : '' }}>Weekly</option>
                <option value="monthly" {{ old('recurring_schedule', $content->recurring_schedule) === 'monthly' ? 'selected' : '' }}>Monthly</option>
            </select>
        </div>
    </div>
    @endif
</div>
