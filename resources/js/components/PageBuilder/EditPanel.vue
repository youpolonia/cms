<template>
  <div class="edit-panel">
    <div class="panel-header">
      <h4 v-if="selectedBlock">
        Editing {{ selectedBlock.type }} Block
      </h4>
      <h4 v-else>
        No Block Selected
      </h4>
    </div>

    <div class="panel-body">
      <component
        v-if="selectedBlock"
        :is="`${selectedBlock.type}-editor`"
        :block="selectedBlock"
        @update:block="$emit('update-block', $event)"
      />
      <div v-else class="empty-state">
        <p>Select a block to edit its properties</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    selectedBlock: Object
  },
  emits: ['update-block']
}
</script>

<style scoped>
.edit-panel {
  width: 300px;
  border-left: 1px solid #eee;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.panel-header {
  padding: 1rem;
  border-bottom: 1px solid #eee;
}

.panel-body {
  padding: 1rem;
  flex: 1;
  overflow-y: auto;
}

.empty-state {
  color: #666;
  text-align: center;
  padding: 2rem;
}
</style>