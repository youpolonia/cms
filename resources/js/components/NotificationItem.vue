<template>
  <div 
    @click="$emit('click')"
    class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition"
    :class="{'bg-blue-50': !notification.read_at}"
  >
    <div class="flex items-start">
      <div class="flex-shrink-0">
        <UserAvatar :user="notification.data.user" size="sm" />
      </div>
      <div class="ml-3 flex-1">
        <p class="text-sm font-medium text-gray-900">
          {{ notification.data.message }}
        </p>
        <p class="text-xs text-gray-500 mt-1">
          {{ formatTime(notification.created_at) }}
        </p>
      </div>
      <div v-if="!notification.read_at" class="ml-2 flex-shrink-0">
        <span class="h-2 w-2 rounded-full bg-blue-500"></span>
      </div>
    </div>
    <div v-if="notification.type === 'App\\Notifications\\CommentNotification'" class="mt-2 text-sm text-gray-700 bg-gray-100 p-2 rounded">
      "{{ notification.data.comment.content }}"
    </div>
  </div>
</template>

<script>
import UserAvatar from './UserAvatar.vue';

export default {
  components: { UserAvatar },
  
  props: {
    notification: {
      type: Object,
      required: true
    }
  },

  methods: {
    formatTime(date) {
      return new Date(date).toLocaleString();
    }
  }
}
</script>