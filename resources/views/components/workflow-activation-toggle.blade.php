@props([
    'workflow' => null,
    'theme' => null,
    'active' => false
])

<div class="flex items-center">
    <form 
        method="POST" 
        action="{{ $active 
            ? route('api.themes.workflows.deactivate', [$theme, $workflow]) 
            : route('api.themes.workflows.activate', [$theme, $workflow]) 
        }}"
        x-data="{ active: {{ $active ? 'true' : 'false' }} }"
        @submit.prevent="fetch($event.target.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                active = !active
                window.dispatchEvent(new CustomEvent('workflow-status-changed', {
                    detail: { workflowId: '{{ $workflow->id }}', active: active }
                }))
            }
        })"
    >
        <button
            type="submit"
            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            :class="active ? 'bg-indigo-600' : 'bg-gray-200'"
            aria-pressed="false"
        >
            <span class="sr-only">Toggle workflow activation</span>
            <span
                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                :class="active ? 'translate-x-6' : 'translate-x-1'"
            ></span>
        </button>
    </form>
    <span class="ml-3 text-sm" :class="active ? 'text-gray-900' : 'text-gray-500'">
        <span x-text="active ? 'Active' : 'Inactive'"></span>
    </span>
</div>
