<template>
  <div class="notification-center">
    <button class="notification-bell" @click="toggleDropdown">
      <span v-if="unreadCount > 0" class="badge">{{ unreadCount }}</span>
      <i class="bell-icon"></i>
    </button>

    <div v-if="showDropdown" class="dropdown">
      <div class="header">
        <h3>Notifications</h3>
        <button @click="markAllAsRead" :disabled="unreadCount === 0">
          Mark all as read
        </button>
      </div>

      <div class="notification-list">
        <div v-for="notification in notifications" 
             :key="notification.id"
             :class="['notification-item', { unread: !notification.read_at }]"
             @click="handleNotificationClick(notification)">
          <div class="notification-icon">
            <i :class="getNotificationIcon(notification.notification_type)"></i>
          </div>
          <div class="notification-content">
            <h4>{{ notification.title }}</h4>
            <p>{{ notification.message }}</p>
            <small>{{ formatDate(notification.created_at) }}</small>
          </div>
          <button v-if="!notification.read_at" 
                  class="mark-read"
                  @click.stop="markAsRead(notification.id)">
            ✓
          </button>
        </div>

        <div v-if="notifications.length === 0" class="empty-state">
          No notifications
        </div>
      </div>

      <div class="footer">
        <router-link to="/notifications">View all</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { playNotificationSound } from '@/utils/sounds';

const router = useRouter();
const showDropdown = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);
const echo = window.Echo;

const getNotificationIcon = (type) => {
  const icons = {
    'system': 'icon-system',
    'approval': 'icon-approval',
    'alert': 'icon-alert'
  };
  return icons[type] || 'icon-default';
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleString();
};

const fetchNotifications = async () => {
  try {
    const response = await axios.get('/api/notifications', {
      params: { per_page: 5 }
    });
    notifications.value = response.data.data;
    updateUnreadCount();
  } catch (error) {
    console.error('Error fetching notifications:', error);
  }
};

const updateUnreadCount = async () => {
  try {
    const response = await axios.get('/api/notifications/unread-count');
    unreadCount.value = response.data.count;
  } catch (error) {
    console.error('Error fetching unread count:', error);
  }
};

const markAsRead = async (id) => {
  try {
    await axios.put(`/api/notifications/${id}/read`);
    await fetchNotifications();
    playNotificationSound('read');
  } catch (error) {
    console.error('Error marking notification as read:', error);
  }
};

const markAllAsRead = async () => {
  try {
    await axios.put('/api/notifications/read-all');
    await fetchNotifications();
    playNotificationSound('read_all');
  } catch (error) {
    console.error('Error marking all notifications as read:', error);
  }
};

const handleNotificationClick = (notification) => {
  if (!notification.read_at) {
    markAsRead(notification.id);
  }
  
  if (notification.data?.route) {
    router.push(notification.data.route);
  }
  
  toggleDropdown();
};

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value;
  if (showDropdown.value) {
    fetchNotifications();
  }
};

const setupEchoListener = () => {
  echo.private(`App.Models.User.${window.userId}`)
    .notification((notification) => {
      fetchNotifications();
      updateUnreadCount();
      playNotificationSound(notification.type);
    });
};

onMounted(() => {
  updateUnreadCount();
  setupEchoListener();
});

onUnmounted(() => {
  echo.leave(`App.Models.User.${window.userId}`);
});
</script>

<style scoped>
.notification-center {
  position: relative;
  display: inline-block;
}

.notification-bell {
  position: relative;
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
}

.badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: red;
  color: white;
  border-radius: 50%;
  width: 18px;
  height: 18px;
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.dropdown {
  position: absolute;
  right: 0;
  top: 100%;
  width: 350px;
  background: white;
  border: 1px solid #ddd;
  border-radius: 4px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  z-index: 1000;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 15px;
  border-bottom: 1px solid #eee;
}

.notification-list {
  max-height: 400px;
  overflow-y: auto;
}

.notification-item {
  display: flex;
  padding: 10px 15px;
  border-bottom: 1px solid #f5f5f5;
  cursor: pointer;
  transition: background 0.2s;
}

.notification-item:hover {
  background: #f9f9f9;
}

.notification-item.unread {
  background: #f0f7ff;
}

.notification-icon {
  margin-right: 10px;
  font-size: 18px;
}

.notification-content {
  flex: 1;
}

.notification-content h4 {
  margin: 0 0 5px 0;
  font-size: 14px;
}

.notification-content p {
  margin: 0 0 5px 0;
  font-size: 13px;
  color: #666;
}

.notification-content small {
  color: #999;
  font-size: 11px;
}

.mark-read {
  background: none;
  border: none;
  color: #4CAF50;
  cursor: pointer;
  font-size: 16px;
  padding: 0 5px;
}

.empty-state {
  padding: 20px;
  text-align: center;
  color: #999;
}

.footer {
  padding: 10px 15px;
  text-align: center;
  border-top: 1px solid #eee;
}

.footer a {
  color: #4CAF50;
  text-decoration: none;
}
</style>