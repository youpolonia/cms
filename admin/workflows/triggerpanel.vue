<template>
  <div class="trigger-panel">
    <h3>Triggers</h3>
    <div class="trigger-list">
      <div 
        v-for="trigger in triggers"
        :key="trigger.id"
        class="trigger-item"
        @click="selectTrigger(trigger)"
      >
        {{ trigger.label }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    triggers: {
      type: Array,
      required: true
    }
  },
  emits: ['add-trigger'],
  methods: {
    selectTrigger(trigger) {
      this.$emit('add-trigger', trigger);
    }
  }
};
</script>

<style scoped>
.trigger-panel {
  padding: 15px;
  background: #f5f5f5;
  border-radius: 4px;
}

.trigger-list {
  margin-top: 10px;
}

.trigger-item {
  padding: 8px 12px;
  margin-bottom: 5px;
  background: white;
  border: 1px solid #ddd;
  border-radius: 3px;
  cursor: pointer;
}

.trigger-item:hover {
  background: #f0f0f0;
}
</style>