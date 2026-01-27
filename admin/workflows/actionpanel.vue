<template>
  <div class="action-panel">
    <h3>Actions</h3>
    <div class="action-list">
      <div 
        v-for="action in actions"
        :key="action.id"
        class="action-item"
        @click="selectAction(action)"
      >
        {{ action.label }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    actions: {
      type: Array,
      required: true
    }
  },
  emits: ['add-action'],
  methods: {
    selectAction(action) {
      this.$emit('add-action', action);
    }
  }
};
</script>

<style scoped>
.action-panel {
  padding: 15px;
  background: #f5f5f5;
  border-radius: 4px;
}

.action-list {
  margin-top: 10px;
}

.action-item {
  padding: 8px 12px;
  margin-bottom: 5px;
  background: white;
  border: 1px solid #ddd;
  border-radius: 3px;
  cursor: pointer;
}

.action-item:hover {
  background: #f0f0f0;
}
</style>