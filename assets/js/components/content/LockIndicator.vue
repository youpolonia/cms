<template>
  <div class="lock-indicator" :class="{'locked': isLocked}">
    <span v-if="isLocked" class="lock-icon">🔒</span>
    <span v-else class="lock-icon">🔓</span>
    
    <span v-if="isLocked" class="lock-details">
      Locked by {{ lockOwner }}
      <span v-if="isCurrentUser" class="your-lock">(You)</span>
    </span>

    <button v-if="showConflictResolution" 
            @click="resolveConflict"
            class="resolve-btn">
      Resolve Conflict
    </button>
  </div>
</template>

<script>
export default {
  props: {
    contentId: {
      type: String,
      required: true
    },
    currentUserId: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      isLocked: false,
      lockOwner: '',
      lockOwnerId: '',
      lastChecked: null
    }
  },
  computed: {
    isCurrentUser() {
      return this.lockOwnerId === this.currentUserId;
    },
    showConflictResolution() {
      return this.isLocked && !this.isCurrentUser;
    }
  },
  methods: {
    async checkLockStatus() {
      try {
        const response = await fetch(`/api/v1/content/${this.contentId}/lock`);
        const data = await response.json();
        
        this.isLocked = data.isLocked;
        this.lockOwner = data.ownerName;
        this.lockOwnerId = data.ownerId;
        this.lastChecked = new Date();
      } catch (error) {
        console.error('Failed to check lock status:', error);
      }
    },
    resolveConflict() {
      this.$emit('resolve-conflict');
    }
  },
  mounted() {
    this.checkLockStatus();
    this.interval = setInterval(this.checkLockStatus, 30000);
  },
  beforeUnmount() {
    clearInterval(this.interval);
  }
}
</script>

<style scoped>
.lock-indicator {
  padding: 8px;
  border-radius: 4px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.locked {
  background-color: #ffebee;
  border: 1px solid #ef9a9a;
}

.lock-icon {
  font-size: 1.2em;
}

.resolve-btn {
  margin-left: 12px;
  padding: 4px 8px;
  background-color: #e3f2fd;
  border: 1px solid #90caf9;
  border-radius: 4px;
  cursor: pointer;
}

.your-lock {
  color: #2e7d32;
  font-weight: bold;
}
</style>