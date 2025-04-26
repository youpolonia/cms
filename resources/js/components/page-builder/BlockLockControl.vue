<template>
  <div class="block-lock-control">
    <button 
      @click="toggleLock"
      :class="{ active: isLocked }"
      :title="isLocked ? 'Unlock block' : 'Lock block'"
    >
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
        <path v-if="isLocked" fill="currentColor" d="M12 17c1.1 0 2-.9 2-2s-.9-2-2-2s-2 .9-2 2s.9 2 2 2zm6-9h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM8.9 6c0-1.71 1.39-3.1 3.1-3.1s3.1 1.39 3.1 3.1v2H8.9V6z"/>
        <path v-else fill="currentColor" d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9V6zm9 14H6V10h12v10zm-6-3c1.1 0 2-.9 2-2s-.9-2-2-2s-2 .9-2 2s.9 2 2 2z"/>
      </svg>
    </button>
  </div>
</template>

<script>
export default {
  props: {
    block: {
      type: Object,
      required: true
    }
  },
  computed: {
    isLocked() {
      return this.block.meta?.locked || false
    }
  },
  methods: {
    toggleLock() {
      this.$emit('lock-toggle', {
        blockId: this.block.id,
        locked: !this.isLocked
      })
    }
  }
}
</script>

<style scoped>
.block-lock-control {
  position: absolute;
  top: 5px;
  right: 5px;
  z-index: 10;
}

button {
  background: white;
  border: 1px solid #ddd;
  border-radius: 4px;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  opacity: 0.7;
  transition: all 0.2s;
}

button:hover {
  opacity: 1;
  background: #f5f5f5;
}

button.active {
  background: #f0f7ff;
  border-color: #007bff;
  color: #007bff;
  opacity: 1;
}
</style>