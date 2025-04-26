<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  users: Array
})

const activeUsers = computed(() => {
  return props.users.filter(user => user.isActive).slice(0, 3)
})

const moreCount = computed(() => {
  return props.users.length - activeUsers.value.length
})
</script>

<template>
  <div class="presence-indicator">
    <div v-for="user in activeUsers" 
         :key="user.id"
         class="user-avatar"
         :style="{background: user.color}">
      {{ user.initials }}
      <div class="user-tooltip">{{ user.name }} (editing)</div>
    </div>
    <div v-if="moreCount > 0" class="more-count">+{{ moreCount }}</div>
  </div>
</template>

<style scoped>
.presence-indicator {
  position: fixed;
  bottom: 16px;
  right: 16px; 
  display: flex;
  align-items: center;
}

.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  position: relative;
  margin-left: -8px;
  box-shadow: 0 0 0 2px var(--color-bg);
}

.user-avatar:hover .user-tooltip {
  opacity: 1;
}

.user-tooltip {
  position: absolute;
  bottom: calc(100% + 8px);
  right: 0;
  background: #242424;
  color: white;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 12px;
  opacity: 0;
  white-space: nowrap;
  pointer-events: none;
  transition: opacity 0.15s;
}

.more-count {
  margin-left: 8px;
  font-size: 14px;
}
</style>