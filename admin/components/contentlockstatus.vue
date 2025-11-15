<template>
  <div class="lock-status" :class="statusClass">
    <span v-if="isLocked">
      🔒 Locked by {{ lockedBy }} (expires {{ expiresIn }})
    </span>
    <span v-else>
      🔓 Available to edit
    </span>
  </div>
</template>

<script>
export default {
  props: {
    contentId: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      isLocked: false,
      lockedBy: '',
      expiresAt: null,
      pollInterval: null
    }
  },
  computed: {
    statusClass() {
      return this.isLocked ? 'locked' : 'available'
    },
    expiresIn() {
      if (!this.expiresAt) return ''
      const mins = Math.ceil((this.expiresAt - Date.now()/1000) / 60)
      return `${mins} min${mins !== 1 ? 's' : ''}`
    }
  },
  methods: {
    async checkLockStatus() {
      try {
        const response = await fetch(`/api/content/${this.contentId}/lock-status`)
        const data = await response.json()
        this.isLocked = data.isLocked
        this.lockedBy = data.lockedBy || ''
        this.expiresAt = data.expiresAt || null
      } catch (error) {
        console.error('Failed to check lock status:', error)
      }
    }
  },
  mounted() {
    this.checkLockStatus()
    this.pollInterval = setInterval(this.checkLockStatus, 30000)
  },
  beforeUnmount() {
    clearInterval(this.pollInterval)
  }
}
</script>

<style scoped>
.lock-status {
  padding: 8px;
  border-radius: 4px;
  font-size: 14px;
}
.locked {
  background-color: #fff3f3;
  color: #d32f2f;
}
.available {
  background-color: #f3f8ff;
  color: #1976d2;
}
</style>