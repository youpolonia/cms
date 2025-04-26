<template>
  <div class="relative">
    <button @click="toggleNotifications" class="p-2 rounded-full hover:bg-gray-100">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>
      <span v-if="unreadCount > 0" class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
        {{ unreadCount }}
      </span>
    </button>

    <div v-if="showNotifications" class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg overflow-hidden z-50">
      <div class="p-4 border-b">
        <h3 class="font-medium">Notifications</h3>
      </div>
      <div class="max-h-96 overflow-y-auto">
        <NotificationItem 
          v-for="notification in notifications"
          :key="notification.id"
          :notification="notification"
          @click="markAsRead(notification)"
        />
      </div>
    </div>
  </div>
</template>

<script>
import NotificationItem from './NotificationItem.vue';

export default {
  components: { NotificationItem },
  
  data() {
    return {
      showNotifications: false,
      notifications: [],
      unreadCount: 0
    }
  },

  mounted() {
    this.fetchNotifications();
    this.listenForNewNotifications();
  },

  methods: {
    toggleNotifications() {
      this.showNotifications = !this.showNotifications;
    },

    async fetchNotifications() {
      const response = await axios.get('/api/notifications');
      this.notifications = response.data;
      this.unreadCount = this.notifications.filter(n => !n.read_at).length;
    },

    async markAsRead(notification) {
      if (!notification.read_at) {
        await axios.patch(`/api/notifications/${notification.id}/read`);
        this.unreadCount--;
      }
      // Handle notification click (e.g. navigate to URL)
    },

    listenForNewNotifications() {
      window.Echo.private(`App.Models.User.${this.$page.props.user.id}`)
        .notification((notification) => {
          this.notifications.unshift(notification);
          this.unreadCount++;
        });
    }
  }
}
</script>