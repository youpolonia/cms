<template>
  <div class="selection-controls" v-if="selectionManager.selectedCount > 0">
    <div class="selection-count">
      {{ selectionManager.selectedCount }} selected
    </div>
    <div class="selection-actions">
      <button @click="copySelected">Copy</button>
      <button @click="deleteSelected">Delete</button>
      <button @click="duplicateSelected">Duplicate</button>
      <button @click="selectionManager.clearSelection()">Clear</button>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    selectionManager: {
      type: Object,
      required: true
    },
    blocks: {
      type: Array,
      required: true  
    }
  },
  methods: {
    copySelected() {
      const selected = this.selectionManager.getSelectedBlocks(this.blocks)
      this.$emit('copy', selected)
    },
    deleteSelected() {
      const selectedIds = Array.from(this.selectionManager.selectedBlocks.value)
      this.$emit('delete', selectedIds)
    },
    duplicateSelected() {
      const selected = this.selectionManager.getSelectedBlocks(this.blocks)
      this.$emit('duplicate', selected)
    }
  }
}
</script>

<style scoped>
.selection-controls {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  background: white;
  padding: 10px 20px;
  border-radius: 5px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  display: flex;
  align-items: center;
  gap: 10px;
  z-index: 1000;
}

.selection-actions {
  display: flex;
  gap: 8px;
}

button {
  padding: 5px 10px;
  border: 1px solid #ddd;
  border-radius: 3px;
  background: #f5f5f5;
  cursor: pointer;
}

button:hover {
  background: #eee;
}
</style>