<template>
  <div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
      <h2 class="text-lg font-medium text-gray-900">
        {{ form.id ? 'Edit' : 'Create' }} Content Schedule
      </h2>
    </div>

    <form @submit.prevent="submit" class="px-6 py-4">
      <div class="grid grid-cols-1 gap-6">
        <div>
          <label for="publish_at" class="block text-sm font-medium text-gray-700">Publish Date/Time</label>
          <input
            type="datetime-local"
            id="publish_at"
            v-model="form.publish_at"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            required
          />
        </div>

        <div>
          <label for="unpublish_at" class="block text-sm font-medium text-gray-700">Unpublish Date/Time (Optional)</label>
          <input
            type="datetime-local"
            id="unpublish_at"
            v-model="form.unpublish_at"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>

        <div>
          <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
          <textarea
            id="notes"
            v-model="form.notes"
            rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          ></textarea>
        </div>

        <div class="flex justify-end space-x-3">
          <button
            type="button"
            @click="cancel"
            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            Save
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  contentId: {
    type: Number,
    required: true
  },
  schedule: {
    type: Object,
    default: null
  }
})

const form = reactive({
  id: props.schedule?.id || null,
  content_id: props.contentId,
  publish_at: props.schedule?.publish_at || '',
  unpublish_at: props.schedule?.unpublish_at || '',
  notes: props.schedule?.notes || ''
})

const submit = () => {
  if (form.id) {
    router.put(route('content.schedules.update', form.id), form)
  } else {
    router.post(route('content.schedules.store'), form)
  }
}

const cancel = () => {
  router.visit(route('content.schedules.index', { content: form.content_id }))
}
</script>