<template>
  <div class="border rounded-lg p-4 mb-4 bg-white shadow-sm">
    <div class="flex justify-between items-start">
      <div class="flex items-center space-x-2">
        <img 
          :src="comment.user.avatar_url || '/images/default-avatar.png'" 
          class="w-8 h-8 rounded-full"
          :alt="comment.user.name"
        >
        <div>
          <p class="font-medium text-gray-900">{{ comment.user.name }}</p>
          <p class="text-xs text-gray-500">{{ formatDate(comment.created_at) }}</p>
        </div>
      </div>
      
      <div v-if="canEdit" class="flex space-x-2">
        <button 
          @click="editComment"
          class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
        >
          Edit
        </button>
        <button 
          @click="deleteComment"
          class="text-red-600 hover:text-red-900 text-sm font-medium"
        >
          Delete
        </button>
      </div>
    </div>

    <div class="mt-3 text-sm text-gray-700">
      <p>{{ comment.comment }}</p>
    </div>

    <div v-if="comment.diff_range" class="mt-2 text-xs text-gray-500">
      <span class="bg-gray-100 px-2 py-1 rounded">
        Lines {{ comment.diff_range.start_line }} to {{ comment.diff_range.end_line }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  comment: Object
})

const emit = defineEmits(['edit'])

const currentUser = computed(() => usePage().props.auth.user)
const canEdit = computed(() => currentUser.value?.id === props.comment.user_id || currentUser.value?.roles?.includes('admin'))

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleString()
}

const editComment = () => {
  emit('edit', props.comment)
}

const deleteComment = async () => {
  if (confirm('Are you sure you want to delete this comment?')) {
    await router.delete(`/api/comments/${props.comment.id}`, {
      preserveScroll: true
    })
  }
}
</script>