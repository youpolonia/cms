<template>
  <div class="workflow-node" :class="node.type">
    <div class="node-header">
      <span class="node-type">{{ nodeTypeLabel }}</span>
      <button class="remove-btn" @click.stop="$emit('remove')">×</button>
    </div>
    <div class="node-content">
      <h4>{{ node.config.label }}</h4>
      <div v-if="node.type === 'trigger'">
        <p>Trigger conditions will go here</p>
      </div>
      <div v-else>
        <p>Action configuration will go here</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    node: {
      type: Object,
      required: true
    }
  },
  computed: {
    nodeTypeLabel() {
      return this.node.type === 'trigger' ? 'Trigger' : 'Action';
    }
  }
};
</script>

<style scoped>
.workflow-node {
  background: white;
  border-radius: 4px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  margin-bottom: 15px;
  overflow: hidden;
}

.node-header {
  display: flex;
  justify-content: space-between;
  padding: 8px 12px;
  background: #e0e0e0;
  font-weight: bold;
}

.trigger .node-header {
  background: #ffd54f;
}

.action .node-header {
  background: #4fc3f7;
}

.remove-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  font-size: 18px;
  line-height: 1;
}

.node-content {
  padding: 12px;
}

.node-content h4 {
  margin: 0 0 8px 0;
}
</style>