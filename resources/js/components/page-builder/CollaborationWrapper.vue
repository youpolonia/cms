<template>
  <div class="collab-wrapper">
    <aside class="collaborators-panel">
      <div class="collaborators-header">
        <h4>Collaborators ({{activeUsers.length}})</h4>
      </div>
      <ul class="collaborators-list">
        <li v-for="user in activeUsers" :key="user.id" class="collaborator">
          <span class="avatar" :style="{'background-color': user.color}">
            {{user.name.charAt(0)}}
          </span>
          <span class="name">{{user.name}}</span>
          <span v-if="isSomeoneTyping(user.id)" class="typing-indicator">
            <span class="dot"></span>
            <span class="dot"></span>
            <span class="dot"></span>
          </span>
        </li>
      </ul>
      <div class="low-latency" :class="{warn: latencyWarning}">
        Latency: {{latency}}ms
      </div>
    </aside>

    <!-- Default slot for wrapped content -->
    <slot :collabData="collabData"></slot>

    <conflict-modal 
      v-if="showConflictModal"
      :changes="pendingChanges"
      @accept-changes="applyRemoteChanges"
      @discard-changes="discardRemoteChanges"
      @resolve-manually="manualConflictResolution"
    />
  </div>
</template>

<script>
import { onMounted, onBeforeUnmount, ref, computed } from 'vue'
import Modal from './ConflictModal.vue'
import { usePageStore } from '@/stores/page'

export default {
  components: { ConflictModal: Modal },

  setup() {
    const pageStore = usePageStore()
    
    // Reactive state
    const collabData = ref({
      users: [],
      latency: 0,
      typingUser: null,
      conflicts: []
    })
    
    // Computed properties
    const activeUsers = computed(() => {
      return collabData.value.users.filter(user => user.active)
    })
    
    const latency = computed(() => collabData.value.latency)
    const latencyWarning = computed(() => latency.value > 500)
    
    function isSomeoneTyping(userId) {
      return collabData.value.typingUser === userId
    }

    // Lifecycle hooks
    onMounted(() => {
      // Initialize web socket connection
      pageStore.initCollaborationSocket()
      
      // Set up event listeners
      document.addEventListener('mousemove', handleUserActivity)
    })

    onBeforeUnmount(() => {
      // Clean up connections
      pageStore.disconnectCollaboration()
      document.removeEventListener('mousemove', handleUserActivity)
    })

    // Event handlers
    function handleUserActivity() {
      pageStore.indicateUserActivity()
    }
    
    function applyRemoteChanges(changes) {
      pageStore.mergeRemoteChanges(changes)
    }
    
    function discardRemoteChanges() {
      // Keep local version 
      showConflictModal.value = false
    }
    
    function manualConflictResolution() {
      // Open advanced conflict resolution UI
    }

    return {
      collabData,
      activeUsers,
      latency,
      latencyWarning,
      isSomeoneTyping,
      applyRemoteChanges,
      discardRemoteChanges,
      manualConflictResolution,
    }
  }
}
</script>

<style scoped>
.collab-wrapper {
  display: flex;
  height: 100%;
  position: relative;
}

.collaborators-panel {
  width: 220px;
  border-right: 1px solid #eee;
  padding: 1rem;
  display: flex;
  flex-direction: column;
}

.collaborators-header {
  padding-bottom: 1rem;
  border-bottom: 1px solid #eee;
}

.collaborators-list {
  flex: 1;
  margin-top: 1rem;
}

.collaborator {
  display: flex;
  align-items: center;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f5f5f5;
}

.avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  margin-right: 0.75rem;
}

.name {
  flex: 1;
  font-size: 0.9rem;
}

.typing-indicator {
  display: flex;
  gap: 3px;
}

.dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: #0066cc;
  animation: pulse 1.5s infinite ease-in-out;
}

.dot:nth-child(2) {
  animation-delay: 0.2s;
}

.dot:nth-child(3) {
  animation-delay: 0.4s;
}

.low-latency {
  margin-top: auto;
  color: #4CAF50;
  font-size: 0.75rem;
  padding-top: 1rem;
  text-align: center;
}

.low-latency.warn {
  color: #FF9800;
}
</style>