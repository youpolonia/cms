<template>
  <form @submit.prevent="submitComment" class="space-y-4">
    <div>
      <label for="comment" class="block text-sm font-medium text-gray-700">Comment</label>
      <textarea
        id="comment"
        v-model="form.comment"
        rows="3"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        required
      ></textarea>
    </div>

    <div class="flex justify-end space-x-3">
      <button
        v-if="editing"
        @click="cancelEdit"
        type="button"
        class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
      >
        Cancel
      </button>
      <button
        type="submit"
        class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
      >
        {{ editing ? 'Update' : 'Post' }} Comment
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  contentId: Number,
  version1Id: Number,
  version2Id: Number,
  content1Hash: String,
  content2Hash: String,
  diffRange: Object,
  comment: Object
})

const emit = defineEmits(['submitted', 'cancelled'])

const editing = ref(!!props.comment)

const form = useForm({
  content_id: props.contentId,
  version1_id: props.version1Id,
  version2_id: props.version2Id,
  content1_hash: props.content1Hash,
  content2_hash: props.content2Hash,
  diff_range: props.diffRange,
  comment: props.comment?.comment || ''
})

const submitComment = () => {
  const url = editing.value 
    ? `/api/comments/${props.comment.id}`
    : '/api/comments'

  const method = editing.value ? 'put' : 'post'

  form.submit(method, url, {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      emit('submitted')
    }
  })
}

const cancelEdit = () => {
  emit('cancelled')
}
</script>